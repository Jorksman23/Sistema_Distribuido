<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CarritoModel;
use App\Services\ProformaGenerator;
use App\Models\FormaPago;
use App\Repositories\CartRepository;

use Throwable;

class PaymentService
{
    protected CarritoModel $carrito;
    protected CartRepository $cartRepository;
    protected ProformaGenerator $proforma;

    public function __construct(
        CarritoModel $carrito,
        CartRepository $cartRepository,
        ProformaGenerator $proforma
    ) {
        $this->carrito = $carrito;
        $this->cartRepository = $cartRepository;
        $this->proforma = $proforma;
    }

    public function procesarPago(array $data,string $codCliente,string $empresa): string {
        DB::connection('odbc')->beginTransaction();
        try {
            $items = $this->carrito->getCarritoByUser($codCliente);
            if (empty($items)) {
                throw new \Exception('El carrito está vacío');
            }
            $granTotal = $this->cartRepository->getTotal($codCliente);
            $codigoOrden = $this->generarCodigoOrden($empresa);
            // Insertar orden
            DB::connection('odbc')->table('DBA.PW_ORDENES_WEB')->insert([
                    'codigo'             => $codigoOrden,
                    'cod_cliente'        => $codCliente,
                    'n_documento'        => $codigoOrden,
                    'tipo'               => companyDefaultOrderType('invoice'),
                    'empresa'            => $empresa,
                    'uuid_session'       => md5(uniqid(rand(), true)),
                    // Ya viene validado desde el controller
                    'tipo_pago'          => $data['tipo_pago'],
                    'items_carrito'      => count($items),
                    'gran_total'         => $granTotal,
                    'estatus'            => '1',
                    'cedula_cliente'     => $data['cedula'],
                    'nombre_cliente'     => $data['nombre'],
                    'email_cliente'      => $data['email'],
                    'telefono_cliente'   => $data['telefono'],
                    'direccion_cliente'  => $data['direccion'],
                    'observacion_compra' => $data['observacion'] ?? null,
                    'fecha_creacion'     => now(),
                    'fecha_modificacion' => now(),
                ]);
            // Generar proforma
            $documento = $this->proforma->generarDesdeOrden((object) [
                    'codigo' => $codigoOrden,
                    'observacion_compra' => $data['observacion'] ?? null,
                ],
                $items,
                $empresa
            );
            // Marcar carrito como procesado
            $this->cartRepository->marcarComoProcesado(
                $codCliente,
                $codigoOrden
            );
            DB::connection('odbc')->commit();
            return $codigoOrden;
        } catch (Throwable $e) {
            DB::connection('odbc')->rollBack();
            Log::error(
                'ERROR PROCESAR PAGO: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    private function generarCodigoOrden(string $empresa): string
    {
        $max = DB::connection('odbc')->selectOne("
            SELECT MAX(CAST(codigo AS INTEGER)) as maxc
            FROM DBA.PW_ORDENES_WEB WHERE empresa = ?
        ", [$empresa]);

        return str_pad(($max->maxc ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }
}
