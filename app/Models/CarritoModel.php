<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class CarritoModel
{
    protected $connection = 'odbc';
    protected $table = 'DBA.pw_carrito_web';

    public function getCarritoByUser(string $userId): array {
        return DB::connection($this->connection)
            ->select("SELECT * FROM {$this->table} WHERE cod_cliente = ?", [$userId]);
    }

   

    public function updateProducto(string $userId, int $productoId, int $cantidad): int {
        return DB::connection($this->connection)->update("
            UPDATE {$this->table}
            SET cantidad = ?
            WHERE orden_id = ? AND producto_id = ?
        ", [$cantidad, $userId, $productoId]);
    }





    public function addProducto(array $data): bool
    {
        return DB::connection($this->connection)->insert("
            INSERT INTO {$this->table}
            (codigo_item, nombre, costo_real, pvp3, cantidad, imagen, estatus, cod_cliente, iva, presentacion)
            VALUES (?, ?, ?, ?, ?, ?, 'A', ?, ?, ?)
        ", [
            $data['codigo_item'],
            $data['nombre'],
            $data['costo_real'],
            $data['pvp3'],
            $data['cantidad'],
            $data['imagen']      ?? null,
            $data['cod_cliente'],
            $data['iva']         ?? null,
            $data['presentacion']?? null,
        ]);
    }

    public function updateCantidad(int $idItemWeb, int $cantidad): int
    {
        return DB::connection($this->connection)->update("
            UPDATE {$this->table} SET cantidad = ? WHERE id_item_web = ?
        ", [$cantidad, $idItemWeb]);
    }

    public function removeProducto(int $idItemWeb): int
    {
        return DB::connection($this->connection)->delete("
            DELETE FROM {$this->table} WHERE id_item_web = ?
        ", [$idItemWeb]);
    }
}
