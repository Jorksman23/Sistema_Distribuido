<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class CartRepository
{
    protected $connection = 'odbc';
    protected $table = 'DBA.pw_carrito_web';

    //Obtener carrito activo de un cliente
    public function getByUser(string $codCliente): array{
        return DB::connection($this->connection)->select("
            SELECT *
            FROM {$this->table}
            WHERE cod_cliente = ?
            AND estatus = '1'
        ", [$codCliente]);
    }

    //Existe producto en el carrito
    public function exists(string $codCliente,string $codigoItem,int $presentacion): bool {
        $row = DB::connection($this->connection)
            ->selectOne("
                SELECT TOP 1 id_item_web
                FROM {$this->table}
                WHERE cod_cliente = ?
                AND codigo_item = ?
                AND presentacion = ?
                AND estatus = '1'
            ", [
                $codCliente,
                $codigoItem,
                $presentacion
            ]);
        return $row !== null;
    }

    //Eliminar producto del carrito
    public function delete(int $idItemWeb,string $codCliente): int {
        return DB::connection($this->connection)
            ->delete("
                DELETE FROM {$this->table}
                WHERE id_item_web = ?
                AND cod_cliente = ?
            ", [
                $idItemWeb,
                $codCliente
            ]);
    }

    // Actualizar cantidad
    public function updateCantidad(int $idItemWeb,string $codCliente,int $cantidad): int {
        return DB::connection($this->connection)->update("
            UPDATE {$this->table}
                SET cantidad = ?
                WHERE id_item_web = ?
                AND cod_cliente = ?
                AND estatus = '1'
            ", [
                $cantidad,
                $idItemWeb,
                $codCliente
            ]);
    }

    // Vaciar carrito
    public function vaciar(string $codCliente): int {
        return DB::connection($this->connection)->delete("
                DELETE FROM {$this->table}
                WHERE cod_cliente = ?
                AND estatus = '1'
            ", [
                $codCliente
            ]);
    }

    // Total carrito
    public function getTotal(string $codCliente): float {
        $row = DB::connection($this->connection)->selectOne("
                SELECT SUM(
                    CAST(pvp3 AS NUMERIC(15,6)) *
                    CAST(cantidad AS INTEGER)
                ) AS total
                FROM {$this->table}
                WHERE cod_cliente = ?
                AND estatus = '1'
            ", [
                $codCliente
            ]);
        return (float) ($row->total ?? 0);
    }

    // Contar items
    public function count(string $codCliente): int {
        $row = DB::connection($this->connection)->selectOne("
                SELECT COUNT(*) AS total
                    FROM {$this->table}
                    WHERE cod_cliente = ?
                    AND estatus = '1'
                ", [
                $codCliente
            ]);
        return (int) ($row->total ?? 0);
    }

    // Marcar procesado
    public function marcarComoProcesado(string $codCliente,string $ordenId): int {
        return DB::connection($this->connection)->update("
            UPDATE {$this->table}
            SET estatus = '2',
                orden_id = ?
            WHERE cod_cliente = ?
            AND estatus = '1'
            ", [
                $ordenId,
                $codCliente
            ]);
    }
}
