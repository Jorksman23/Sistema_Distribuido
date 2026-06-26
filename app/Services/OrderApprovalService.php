<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\PaymentService;
use Throwable;

class OrderApprovalService
{
    protected PaymentService $paymentService;
    protected ProformaGenerator $proforma;

    public function __construct(
        PaymentService $paymentService,
        ProformaGenerator $proforma
    ) {
        $this->paymentService = $paymentService;
        $this->proforma = $proforma;
    }

    public function aprobar(string $codigo, string $empresa) {
        $orden = $this->paymentService->obtenerOrden($codigo, $empresa);

        if (!$orden) {
            throw new \Exception('Orden no encontrada');
        }
        if ($orden->estatus === '2') {
            throw new \Exception('La orden ya fue aprobada anteriormente');
        }

        // Obtener items de la orden para descontar stock
        $items = DB::connection('odbc')->select("
            SELECT codigo_item, cantidad, presentacion, ubicacion
            FROM DBA.pw_carrito_web
            WHERE orden_id = CAST(? AS INTEGER)
            AND estatus = '2'
        ", [$codigo]);

        DB::connection('odbc')->beginTransaction();

        try {
            // Descontar stock por cada item
            foreach ($items as $item) {
                $cantidad     = (int) $item->cantidad;
                $presentacion = (int) ($item->presentacion ?? 0);
                $ubicacion    = $item->ubicacion;

                if ($presentacion > 0) {
                    DB::connection('odbc')->update("
                        UPDATE DBA.in_existencia_presentacion
                        SET cantidad = cantidad - ?
                        WHERE empresa = ? AND item_presentacion = ? AND ubicacion = ?
                    ", [$cantidad, $empresa, $presentacion, $ubicacion]);
                } else {
                    DB::connection('odbc')->update("
                        UPDATE DBA.in_existencia
                        SET existencia = existencia - ?
                        WHERE empresa = ? AND producto = ? AND ubicacion = ?
                    ", [$cantidad, $empresa, $item->codigo_item, $ubicacion]);
                }
            }

            // Actualizar estado de la orden
            DB::connection('odbc')
                ->table('DBA.PW_ORDENES_WEB')
                ->where('codigo', $codigo)
                ->update([
                    'estatus'            => '2',
                    'fecha_modificacion' => now(),
                ]);

            // Registrar histórico
            DB::connection('odbc')
                ->table('DBA.PW_HISTORICO_PEDIDO')
                ->insert([
                    'cod_orden'      => $codigo,
                    'codigo_cliente' => $orden->cod_cliente,
                    'cod_estado'     => '3',
                    'observacion'    => 'Pago aprobado',
                    'fecha_cambio'   => now(),
                    'created_at'     => now(),
                    'update_at'      => now(),
                    'empresa'        => $empresa,
                ]);

            DB::connection('odbc')->commit();

        } catch (Throwable $e) {
            DB::connection('odbc')->rollBack();
            throw new \Exception('Error al aprobar la orden: ' . $e->getMessage());
        }

        return $orden->n_documento;
    }

    public function rechazar(string $codigo,string $empresa,string $motivo){
        $orden = $this->paymentService->obtenerOrden($codigo, $empresa);
        if (!$orden) {
            throw new \Exception('Orden no encontrada');
        }
        DB::connection('odbc')
            ->table('DBA.PW_HISTORICO_PEDIDO')
            ->insert([
                'cod_orden'      => $codigo,
                'codigo_cliente' => $orden->cod_cliente,
                'cod_estado'     => '1',
                'observacion'    => $motivo,
                'fecha_cambio'   => now(),
                'created_at'     => now(),
                'update_at'      => now(),
                'empresa'        => $empresa,
            ]);
    }
}
