<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Services\PaymentMethodService;
use App\Services\CxcAuxiliarProformaService;
use Throwable;

class ComprobanteService
{
    protected CartRepository $cartRepository;
    protected OrderRepository $orderRepository;
    protected PaymentMethodService $paymentMethodService;
    protected CxcAuxiliarProformaService $cxcAuxiliarProformaService;

    public function __construct(
        CartRepository $cartRepository,
        OrderRepository $orderRepository,
        PaymentMethodService $paymentMethodService,
        CxcAuxiliarProformaService $cxcAuxiliarProformaService
    ) {
        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
        $this->paymentMethodService = $paymentMethodService;
        $this->cxcAuxiliarProformaService = $cxcAuxiliarProformaService;
    }

    public function mostrarComprobante(array $checkoutData)
    {
        if (!$checkoutData) {
            return redirect()->route('pedidos.pagar')->withErrors([
                'error' => 'No hay datos de pago.'
            ]);
        }

        $formaPago = $this->paymentMethodService->obtenerFormaPago(
            $checkoutData['tipo_pago'],
            currentCompany()
        );

        $cuentaBanco = $this->paymentMethodService->obtenerCuentaBanco(
            $formaPago,
            currentCompany()
        );

        return view('pedidos.comprobante', [
            'formaPago'   => $formaPago,
            'cuentaBanco' => $cuentaBanco,
        ]);
    }

    public function guardarComprobante($archivo, array $checkoutData, string $codCliente, string $empresa, $items, $granTotal)
    {
        $codigoOrden = $this->orderRepository->generarCodigoOrden($empresa);

        $nombreArchivo = $codigoOrden . '_' . time() . '.' .$archivo->getClientOriginalExtension();
        $ruta = $archivo->storeAs(
            'comprobantes/' . $empresa,
            $nombreArchivo,
            'public'
        );

        try {
            DB::connection('odbc')->beginTransaction();

            // Crear orden
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

            // Registrar en auxiliar proforma
            $this->cxcAuxiliarProformaService->registrar(
                $codigoOrden,
                (int)$checkoutData['tipo_pago'],
                $granTotal,
                $empresa,
                $granTotal,
                'Transferencia web'
            );

            // Guardar adjunto
            DB::connection('odbc')->table('DBA.PW_ADJUNTO_WEB')->insert([
                'empresa'         => $empresa,
                'cod_orden'       => $codigoOrden,
                'cod_cliente'     => $codCliente,
                'foto'            => $ruta,
                'foto_id'         => $nombreArchivo,
                'nombre_archivo'  => $archivo->getClientOriginalName(),
                'tipo_archivo'    => $archivo->getClientOriginalExtension(),
                'created_at'      => now(),
                'update_at'       => now(),
            ]);

            // Historial
            DB::connection('odbc')->table('DBA.PW_HISTORICO_PEDIDO')->insert([
                'cod_orden'      => $codigoOrden,
                'codigo_cliente' => $codCliente,
                'cod_estado'     => '2',
                'observacion'    => 'Comprobante cargado por el cliente',
                'fecha_cambio'   => now(),
                'created_at'     => now(),
                'update_at'      => now(),
                'empresa'        => $empresa,
            ]);

            // Asociar carrito
            $this->cartRepository->marcarComoProcesado($codCliente, $codigoOrden);

            DB::connection('odbc')->commit();
            session()->forget('checkout_data');

            return redirect()->route('profile.show')->with(
                'success',
                'Comprobante enviado correctamente.'
            );
        } catch (Throwable $e) {
            DB::connection('odbc')->rollBack();
            if (!empty($ruta)) {
                Storage::disk('public')->delete($ruta);
            }
            return back()->withErrors([
                'error' => 'Error al guardar el comprobante: ' . $e->getMessage()
            ]);
        }
    }
}
