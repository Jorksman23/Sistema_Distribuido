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
    public function generarCodigoOrden(string $empresa): string
    {
        $max = DB::connection($this->connection)->selectOne("
            SELECT MAX(CAST(codigo AS INTEGER)) AS maxc
            FROM DBA.PW_ORDENES_WEB
            WHERE empresa = ?
        ", [$empresa]);

        $siguiente = ($max && $max->maxc) ? ((int)$max->maxc + 1) : 1;

        return (string)$siguiente;
    }

    // public function obtenerItemsOrden(string $codigo, string $codCliente): array
    // {
    //     return DB::connection('odbc')->select("
    //         SELECT nombre, pvp3, cantidad, presentacion, iva
    //         FROM DBA.pw_carrito_web
    //         WHERE orden_id = CAST(? AS INTEGER)
    //         AND estatus = '2'
    //     ", [$codigo, $codCliente]);
    // }
    public function obtenerItemsOrden(string $codigo, string $codCliente): array
    {
        return DB::connection('odbc')->select("
            SELECT nombre, pvp3, cantidad, presentacion, iva
            FROM DBA.pw_carrito_web
            WHERE orden_id = CAST(? AS INTEGER)
            AND cod_cliente = ?
            AND estatus = '2'
        ", [$codigo, $codCliente]);
    }
}
