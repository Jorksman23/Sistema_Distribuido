<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class CarritoModel {
    protected  $connection = 'odbc';
    protected  $table = 'DBA.pw_carrito_web';

    public function getCarritoByUser(string $userId): array {
        return DB::connection($this->connection)
            ->select("SELECT * FROM {$this->table} WHERE cod_cliente = ?", [$userId]);
    }

    public function addProducto(string $userId, int $productoId, int $cantidad): bool {
        return DB::connection($this->connection)->insert("
            INSERT INTO {$this->table} (orden_id, producto_id, cantidad)
            VALUES (?, ?, ?)
        ", [$userId, $productoId, $cantidad]);
    }

    public function updateProducto(string $userId, int $productoId, int $cantidad): int {
        return DB::connection($this->connection)->update("
            UPDATE {$this->table}
            SET cantidad = ?
            WHERE orden_id = ? AND producto_id = ?
        ", [$cantidad, $userId, $productoId]);
    }

    public function removeProducto(string $userId, int $productoId): int {
        return DB::connection($this->connection)->delete("
            DELETE FROM {$this->table} WHERE orden_id = ? AND producto_id = ?
        ", [$userId, $productoId]);
    }
}
