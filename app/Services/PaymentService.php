<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\CarritoModel;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Services\PaymentMethodService;
use App\Services\CheckoutService;
use App\Services\CxcAuxiliarProformaService;
use App\Services\ProformaGenerator;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentService
{
    protected CarritoModel $carrito;
    protected CartRepository $cartRepository;
    protected CheckoutService $checkoutService;
    protected OrderRepository $orderRepository;
    protected CxcAuxiliarProformaService $cxcAuxiliarProformaService;
    protected PaymentMethodService $paymentMethodService;
    protected ProformaGenerator $proformaGenerator;



    public function __construct(
        CarritoModel $carrito,
        CartRepository $cartRepository,
        CheckoutService $checkoutService,
        OrderRepository $orderRepository,
        CxcAuxiliarProformaService $cxcAuxiliarProformaService,
        PaymentMethodService $paymentMethodService,
        ProformaGenerator $proformaGenerator
    ) {
        $this->carrito = $carrito;
        $this->cartRepository = $cartRepository;
        $this->checkoutService = $checkoutService;
        $this->orderRepository = $orderRepository;
        $this->cxcAuxiliarProformaService = $cxcAuxiliarProformaService;
        $this->paymentMethodService = $paymentMethodService;
        $this->proformaGenerator = $proformaGenerator;
    }

    /**
     * Procesar pago
     */
    public function procesarPago(array $data, string $codCliente, string $empresa){
        $formaPago = $this->paymentMethodService->obtenerFormaPago($data['tipo_pago'], $empresa);
        if (!$formaPago) {
            return back()->withErrors(['error' => 'Forma de pago inválida']);
        }

        try {
            $clienteData       = $this->obtenerOCrearCliente($data, $empresa);
            $codClienteFactura = $clienteData['codigo']; // código de in_cliente para la factura
            // $codCliente sigue siendo session('user_id') para el carrito

            session([
                'checkout_data' => [
                    'empresa'        => $empresa,
                    'tipo_pago'      => $data['tipo_pago'],
                    'cedula'         => $data['cedula'],
                    'nombre'         => $data['nombre'],
                    'email'          => $data['email'],
                    'telefono'       => $data['telefono'],
                    'direccion'      => $data['direccion'],
                    'observacion'    => $data['observacion'],
                    'metodo_entrega' => $data['metodo_entrega'],
                ]
            ]);

            if ((int)$data['tipo_pago'] === 1) {
                return $this->procesarPagoEfectivo(session('checkout_data'), $codClienteFactura, $codCliente, $empresa);
            }

            return $this->procesarPagoTransferencia(session('checkout_data'), $codClienteFactura, $codCliente, $empresa);
        } catch (Throwable $e) {
            return back()->withErrors([
                'error' => 'Error al procesar la compra: ' . $e->getMessage()
            ]);
        }
    }


    public function procesarPagoEfectivo(array $checkoutData, string $codClienteFactura, string $codClienteCarrito, string $empresa){
        $items = $this->carrito->getCarritoByUser($codClienteCarrito);
        if (empty($items)) {
            return redirect()->route('carrito.index')->withErrors([
                'error' => 'El carrito está vacío.'
            ]);
        }

        $codigoOrden = $this->orderRepository->generarCodigoOrden($empresa);
        $checkout  = $this->checkoutService->obtenerCheckout($codClienteCarrito);
        $granTotal = (float) ($checkout['trabajaConIvaIncluido'] === 'S'
            ? $checkout['total']
            : $checkout['totalBruto']);

        try {
            DB::connection('odbc')->beginTransaction();

            DB::connection('odbc')->table('DBA.PW_ORDENES_WEB')->insert([
                'codigo'             => $codigoOrden,
                'cod_cliente'        => $codClienteFactura, // código de in_cliente
                'n_documento'        => $codigoOrden,
                'tipo'               => companyDefaultOrderType('invoice'),
                'empresa'            => $empresa,
                'uuid_session'       => md5(uniqid(rand(), true)),
                'user_id'            => $codClienteCarrito, // user_id de sesión
                'tipo_pago'          => $checkoutData['tipo_pago'],
                'items_carrito'      => count($items),
                'gran_total'         => $granTotal,
                'estatus'            => '1',
                'cedula_cliente'     => $checkoutData['cedula'],
                'nombre_cliente'     => $checkoutData['nombre'],
                'email_cliente'      => $checkoutData['email'],
                'telefono_cliente'   => $checkoutData['telefono'],
                'direccion_cliente'  => $checkoutData['direccion'],
                'observacion_compra' => $checkoutData['observacion'] ?? null,
                'metodo_entrega'     => $checkoutData['metodo_entrega'],
                'fecha_creacion'     => now(),
                'fecha_modificacion' => now(),
            ]);

            $this->cxcAuxiliarProformaService->registrar(
                $codigoOrden,
                (int)$checkoutData['tipo_pago'],
                $granTotal,
                $empresa,
                null,
                'Reserva web - pago en tienda'
            );

            $documento = $this->proformaGenerator->generarDesdeOrden(
                (object)[
                    'codigo'             => $codigoOrden,
                    'observacion_compra' => $checkoutData['observacion']
                ],
                $items,
                $empresa
            );

            DB::connection('odbc')->table('DBA.PW_HISTORICO_PEDIDO')->insert([
                'cod_orden'      => $codigoOrden,
                'codigo_cliente' => $codClienteFactura,
                'cod_estado'     => '1',
                'observacion'    => 'Pedido registrado para pago en efectivo',
                'fecha_cambio'   => now(),
                'created_at'     => now(),
                'update_at'      => now(),
                'empresa'        => $empresa,
            ]);

            $this->cartRepository->marcarComoProcesado($codClienteCarrito, $codigoOrden); // usar user_id

            DB::connection('odbc')->commit();
            session()->forget(['carrito_count', 'carrito_ubicacion']);
            session(['checkout_data' => $checkoutData]);

            return view('pedidos.confirmacion-efectivo', [
                'codigoOrden' => $codigoOrden,
                'total'       => $granTotal
            ]);
        } catch (Throwable $e) {
            DB::connection('odbc')->rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function procesarPagoTransferencia(array $checkoutData, string $codClienteFactura, string $codClienteCarrito, string $empresa){
        $items = $this->carrito->getCarritoByUser($codClienteCarrito);
        if (empty($items)) {
            return redirect()->route('carrito.index')->withErrors([
                'error' => 'El carrito está vacío.'
            ]);
        }

        $codigoOrden = $this->orderRepository->generarCodigoOrden($empresa);
        $checkout    = $this->checkoutService->obtenerCheckout($codClienteCarrito);
        $granTotal   = (float) ($checkout['trabajaConIvaIncluido'] === 'S'
            ? $checkout['total']
            : $checkout['totalBruto']);

        try {
            DB::connection('odbc')->beginTransaction();

            DB::connection('odbc')->table('DBA.PW_ORDENES_WEB')->insert([
                'codigo'             => $codigoOrden,
                'cod_cliente'        => $codClienteFactura, // código de in_cliente
                'n_documento'        => $codigoOrden,
                'tipo'               => companyDefaultOrderType('invoice'),
                'empresa'            => $empresa,
                'uuid_session'       => md5(uniqid(rand(), true)),
                'user_id'            => $codClienteCarrito, // user_id de sesión
                'tipo_pago'          => $checkoutData['tipo_pago'],
                'items_carrito'      => count($items),
                'gran_total'         => $granTotal,
                'estatus'            => '1',
                'cedula_cliente'     => $checkoutData['cedula'],
                'nombre_cliente'     => $checkoutData['nombre'],
                'email_cliente'      => $checkoutData['email'],
                'telefono_cliente'   => $checkoutData['telefono'],
                'direccion_cliente'  => $checkoutData['direccion'],
                'observacion_compra' => $checkoutData['observacion'] ?? null,
                'metodo_entrega'     => $checkoutData['metodo_entrega'],
                'fecha_creacion'     => now(),
                'fecha_modificacion' => now(),
            ]);

            $formaPago   = $this->paymentMethodService->obtenerFormaPago((int)$checkoutData['tipo_pago'], $empresa);
            $cuentaBanco = $this->paymentMethodService->obtenerCuentaBanco($formaPago, $empresa);

            $this->cxcAuxiliarProformaService->registrar(
                $codigoOrden,
                (int)$checkoutData['tipo_pago'],
                $granTotal,
                $empresa,
                null,
                $cuentaBanco ? "Transferencia a banco {$cuentaBanco->descripcion}" : "Transferencia bancaria"
            );

            $documento = $this->proformaGenerator->generarDesdeOrden(
                (object)[
                    'codigo'             => $codigoOrden,
                    'observacion_compra' => $checkoutData['observacion']
                ],
                $items,
                $empresa
            );

            DB::connection('odbc')->table('DBA.PW_HISTORICO_PEDIDO')->insert([
                'cod_orden'      => $codigoOrden,
                'codigo_cliente' => $codClienteFactura, // código de in_cliente
                'cod_estado'     => '1',
                'observacion'    => 'Pedido registrado para transferencia bancaria',
                'fecha_cambio'   => now(),
                'created_at'     => now(),
                'update_at'      => now(),
                'empresa'        => $empresa,
            ]);

            $this->cartRepository->marcarComoProcesado($codClienteCarrito, $codigoOrden); // user_id

            DB::connection('odbc')->commit();

            session()->forget(['checkout_data', 'carrito_count', 'carrito_ubicacion']);
            session([
                'checkout_data' => $checkoutData,
                'codigo_orden'  => $codigoOrden,
                'documento'     => $documento,
            ]);

            return redirect()->route('pedidos.comprobante')->with(
                'success',
                "Orden {$codigoOrden} registrada con documento {$documento}. Ahora suba su comprobante."
            );
        } catch (Throwable $e) {
            DB::connection('odbc')->rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }



    public function obtenerOCrearCliente(array $data, string $empresa): array
    {
        try {
            //  Consultar servicio externo
            $url = "http://186.101.203.79:2001/persona/{$data['cedula']}";
            $response = Http::timeout(5)->get($url);
            $datosServicio = $response->ok() ? $response->json() : [];

            //  Buscar cliente local
            $cliente = DB::connection('odbc')->selectOne("
                SELECT TOP 1 *
                FROM DBA.in_cliente
                WHERE cedula_ruc = ? AND empresa = ?
            ", [$data['cedula'], $empresa]);

            if ($cliente) {
                //  Actualizar datos con servicio + formulario
                DB::connection('odbc')->table('DBA.in_cliente')
                    ->where('codigo', $cliente->codigo)
                    ->where('empresa', $empresa)
                    ->update([
                        'nombre'     => $data['nombre'] ?? $datosServicio['nombre'] ?? $cliente->nombre,
                        'e_mail'     => $data['email'] ?? $cliente->e_mail,
                        'telefono'   => $data['telefono'] ?? $cliente->telefono,
                        'direccion1' => $data['direccion'] ?? $cliente->direccion1,
                    ]);

                // Retornar datos completos
                return [
                    'codigo'     => $cliente->codigo,
                    'cedula'     => $cliente->cedula_ruc,
                    'nombre'     => $data['nombre'] ?? $datosServicio['nombre'] ?? $cliente->nombre,
                    'email'      => $data['email'] ?? $cliente->e_mail,
                    'telefono'   => $data['telefono'] ?? $cliente->telefono,
                    'direccion'  => $data['direccion'] ?? $cliente->direccion1,
                ];
            }

            // Crear nuevo cliente si no existe (código único por empresa)
            $maxCodigo = DB::connection('odbc')->selectOne("
                SELECT COALESCE(MAX(CAST(codigo AS INT)), 0) + 1 AS nuevo_codigo
                FROM DBA.in_cliente
                WHERE empresa = ?
            ", [$empresa]);

            $nuevoCodigo = (int)$maxCodigo->nuevo_codigo;

            DB::connection('odbc')->table('DBA.in_cliente')->insert([
                'codigo'     => $nuevoCodigo,
                'cedula_ruc' => $data['cedula'],
                'nombre'     => $data['nombre'] ?? $datosServicio['nombre'] ?? '',
                'e_mail'     => $data['email'] ?? '',
                'telefono'   => $data['telefono'] ?? '',
                'direccion1' => $data['direccion'] ?? '',
                'empresa'    => $empresa,
                'estado'     => 'A',
                'vendedor'   => '1'
            ]);

            //  Retornar datos completos
            return [
                'codigo'     => $nuevoCodigo,
                'cedula'     => $data['cedula'],
                'nombre'     => $data['nombre'] ?? $datosServicio['nombre'] ?? '',
                'email'      => $data['email'] ?? '',
                'telefono'   => $data['telefono'] ?? '',
                'direccion'  => $data['direccion'] ?? '',
            ];

        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function obtenerOrden(string $codigo,string $empresa){
        return DB::connection('odbc')->selectOne("
            SELECT *
            FROM DBA.PW_ORDENES_WEB
            WHERE codigo = ?
            AND empresa = ?
        ", [
            $codigo,
            $empresa
        ]);
    }
}
