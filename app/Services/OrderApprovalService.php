<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\PaymentService;

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
    if ($orden->estatus === '2') {
        throw new \Exception('La orden ya fue aprobada anteriormente');
    }

    // Solo actualizar estado de la orden
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
