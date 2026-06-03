<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

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

    public function aprobar(string $codigo,string $empresa) {
        $orden = $this->paymentService->obtenerOrden($codigo, $empresa);

        if (!$orden) {
            throw new \Exception('Orden no encontrada');
        }
        //Evitar aprobar dos veces la misma orden
        if ($orden->estatus === '2') {
            throw new \Exception('La orden ya fue aprobada anteriormente');
        }
        $items = DB::connection('odbc')->select("
            SELECT *
            FROM DBA.PW_CARRITO_WEB
            WHERE orden_id = ?
            AND cod_cliente = ?
        ", [
            (int)$codigo,$orden->cod_cliente
        ]);

        $documento = $this->proforma->generarDesdeOrden(
            $orden,
            $items,
            $empresa
        );

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

        return $documento;
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
