<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CxcAuxiliarProformaService
{
    protected string $connection = 'odbc';

    public function registrar(string $documento,int $formaPago,float $valor,string $empresa,?float $anticipo = null,?string $observacion = null): void {
        DB::connection($this->connection)
            ->table('DBA.CXC_AUXILIAR_PROFORMA')
            ->insert([
                'documento'   => $documento,
                'tipo'        => 'TW',
                'forma_pago'  => $formaPago,
                'fechae'      => now()->toDateString(),
                'fechav'      => now()->toDateString(),
                'valor'       => $valor,
                'banco'       => 0,
                'empresa'     => $empresa,
                'anticipo'    => $anticipo,
                'observacion' => $observacion,
            ]);
    }
}
