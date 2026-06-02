<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class OrderRepository
{
    protected string $connection = 'odbc';

    // Crear orden web
    public function crearOrden(array $data): bool
    {
        return DB::connection($this->connection)
            ->table('DBA.PW_ORDENES_WEB')
            ->insert($data);
    }

    // Generar Orden
    public function generarCodigoOrden(string $empresa): string{
        $max = DB::connection($this->connection)
            ->selectOne("
                SELECT MAX(CAST(codigo AS INTEGER)) as maxc
                FROM DBA.PW_ORDENES_WEB
                WHERE empresa = ?
            ", [$empresa]);

        return str_pad(($max->maxc ?? 0) + 1,6,'0',STR_PAD_LEFT
        );
    }
}
