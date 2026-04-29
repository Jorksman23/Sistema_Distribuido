<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class CarritoModel {
    protected string $connection = 'odbc';
    protected string $table = 'DBA.pw_carrito_web';

    public function getCarritoByUser(int $userId): array {
        return DB::connection($this->connection)
            ->select("SELECT * FROM {$this->table} WHERE usuario_id = ?", [$userId]);
    }

    public function addProducto(int $userId, int $productoId, int $cantidad): bool {
        return DB::connection($this->connection)->insert("
            INSERT INTO {$this->table} (usuario_id, producto_id, cantidad)
            VALUES (?, ?, ?)
        ", [$userId, $productoId, $cantidad]);
    }

    public function updateProducto(int $userId, int $productoId, int $cantidad): int {
        return DB::connection($this->connection)->update("
            UPDATE {$this->table}
            SET cantidad = ?
            WHERE usuario_id = ? AND producto_id = ?
        ", [$cantidad, $userId, $productoId]);
    }

    public function removeProducto(int $userId, int $productoId): int {
        return DB::connection($this->connection)->delete("
            DELETE FROM {$this->table} WHERE usuario_id = ? AND producto_id = ?
        ", [$userId, $productoId]);
    }
}