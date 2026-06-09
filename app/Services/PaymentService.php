<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\CarritoModel;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Services\PaymentMethodService;
use App\Services\CheckoutService;

use App\Services\CxcAuxiliarProformaService;
use Throwable;

class PaymentService
{
    protected CarritoModel $carrito;
    protected CartRepository $cartRepository;
    protected CheckoutService $checkoutService;
    protected OrderRepository $orderRepository;
    protected CxcAuxiliarProformaService $cxcAuxiliarProformaService;
    protected PaymentMethodService $paymentMethodService;


    public function __construct(
        CarritoModel $carrito,
        CartRepository $cartRepository,
        CheckoutService $checkoutService,
        OrderRepository $orderRepository,
        CxcAuxiliarProformaService $cxcAuxiliarProformaService,
        PaymentMethodService $paymentMethodService,
    ) {
        $this->carrito = $carrito;
        $this->cartRepository = $cartRepository;
        $this->checkoutService = $checkoutService;
        $this->orderRepository = $orderRepository;
        $this->cxcAuxiliarProformaService = $cxcAuxiliarProformaService;
        $this->paymentMethodService = $paymentMethodService;
    }

    /**
     * Procesar pago
     */
    public function procesarPago(array $data, string $codCliente, string $empresa)
    {
        // Validar forma de pago
        $formaPago = $this->paymentMethodService->obtenerFormaPago($data['tipo_pago'], $empresa);
        if (!$formaPago) {
            return back()->withErrors(['error' => 'Forma de pago inválida']);
        }

        try {
            // Guardar temporalmente datos del checkout
            session([
                'checkout_data' => [
                    'empresa'     => $empresa,
                    'tipo_pago'   => $data['tipo_pago'],
                    'cedula'      => $data['cedula'],
                    'nombre'      => $data['nombre'],
                    'email'       => $data['email'],
                    'telefono'    => $data['telefono'],
                    'direccion'   => $data['direccion'],
                    'observacion' => $data['observacion'],
                ]
            ]);

            // EFECTIVO
            if ((int)$data['tipo_pago'] === 1) {
                return $this->procesarPagoEfectivo(session('checkout_data'), $codCliente, $empresa);
            }

            // TRANSFERENCIAS Y DEMÁS FORMAS DE PAGO
            return redirect()->route('pedidos.comprobante')->with(
                'success',
                'Ahora suba su comprobante de pago.'
            );
        } catch (Throwable $e) {
            return back()->withErrors([
                'error' => 'Error al procesar la compra: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Procesar pago en efectivo
     */
    public function procesarPagoEfectivo(array $checkoutData, string $codCliente, string $empresa)
    {
        $items = $this->carrito->getCarritoByUser($codCliente);
        if (empty($items)) {
            return redirect()->route('carrito.index')->withErrors([
                'error' => 'El carrito está vacío.'
            ]);
        }

        $codigoOrden = $this->orderRepository->generarCodigoOrden($empresa);
       $checkout = $this->checkoutService->obtenerCheckout($codCliente);
        $granTotal   = (float) $checkout['total'];

        try {
            DB::connection('odbc')->beginTransaction();

            DB::connection('odbc')->table('DBA.PW_ORDENES_WEB')->insert([
                'codigo'             => $codigoOrden,
                'cod_cliente'        => $codCliente,
                'n_documento'        => $codigoOrden,
                'tipo'               => companyDefaultOrderType('invoice'),
                'empresa'            => $empresa,
                'uuid_session'       => md5(uniqid(rand(), true)),
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

            DB::connection('odbc')->table('DBA.PW_HISTORICO_PEDIDO')->insert([
                'cod_orden'      => $codigoOrden,
                'codigo_cliente' => $codCliente,
                'cod_estado'     => '1',
                'observacion'    => 'Pedido registrado para pago en efectivo',
                'fecha_cambio'   => now(),
                'created_at'     => now(),
                'update_at'      => now(),
                'empresa'        => $empresa,
            ]);

            $this->cartRepository->marcarComoProcesado($codCliente, $codigoOrden);

            DB::connection('odbc')->commit();
            session()->forget('checkout_data');

            return view('pedidos.confirmacion-efectivo', [
                'codigoOrden' => $codigoOrden,
                'total'       => $granTotal
            ]);
        } catch (Throwable $e) {
            DB::connection('odbc')->rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
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
