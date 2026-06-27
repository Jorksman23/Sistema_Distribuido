<?php

namespace App\Services;

use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Services\PaymentMethodService;
use App\Services\CxcAuxiliarProformaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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


    public function guardarComprobante($archivo,array $checkoutData,string $codCliente,string $empresa,$items,$granTotal)
    {
        $codigoOrden = session('codigo_orden');
        $documento   = session('documento');

        if (!$codigoOrden || !$documento) {
            return back()->withErrors([
                'error' => 'No se encontró la orden registrada. Vuelva a procesar el pago.'
            ]);
        }

        $companyCode   = currentCompany();
        $rucEmpresa    = companyRuc($companyCode);
        $nombreArchivo = 'pruebapasante_' . $codigoOrden;

        // Validar archivo antes de convertir
        if (!$archivo->isValid()) {
            return back()->withErrors([
                'error' => 'El archivo de comprobante no es válido.'
            ]);
        }

        // Leer contenido y convertir a base64 con prefijo MIME
        $mimeType = $archivo->getMimeType();
        $contenido = file_get_contents($archivo->getPathname());
        if ($contenido === false) {
            return back()->withErrors([
                'error' => 'No se pudo leer el archivo de comprobante.'
            ]);
        }

        $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($contenido);

        // Payload para el servicio externo
        $payload = [
            'files' => [
                [
                    'image' => $base64,
                    'name'  => $nombreArchivo,
                ]
            ],
            'ruc'       => $rucEmpresa,
            'reference' => 'comprobante',
        ];

        try {
            DB::connection('odbc')->beginTransaction();
            $response = Http::post('http://186.101.203.76:10555/image/upload/', $payload);

            if (!$response->successful()) {
                throw new \Exception('Error al subir al servidor externo');
            }

            $remoteData = $response->json();
            if (empty($remoteData['data'][0]['url']) || empty($remoteData['data'][0]['name'])) {
                throw new \Exception('El servidor externo no devolvió datos válidos');
            }

            $fotoId   = $remoteData['data'][0]['url'];
            $fotoName = $remoteData['data'][0]['name'];


            // Guardar adjunto en tu BD con datos del servidor externo
            DB::connection('odbc')->table('DBA.PW_ADJUNTO_WEB')->insert([
                'empresa'         => $empresa,
                'cod_orden'       => $codigoOrden,
                'cod_cliente'     => $codCliente,
                'foto'            => $fotoName,
                'foto_id'         => $fotoId,
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

            session()->forget(['checkout_data','carrito_count','carrito_ubicacion']);
            return redirect()->route('profile.orders')->with(
                'success',
                "Comprobante enviado correctamente para la orden {$codigoOrden} con documento {$documento}."
            );
        } catch (Throwable $e) {
            DB::connection('odbc')->rollBack();

            // Fallback: guardar local si falla
            $archivo->storeAs(
                'comprobantes/' . $empresa,
                $nombreArchivo,
                'public'
            );

            return back()->withErrors([
                'error' => 'Error al guardar el comprobante: ' . $e->getMessage()
            ]);
        }
    }
}
