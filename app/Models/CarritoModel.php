<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class CarritoModel
{
    protected $connection = 'odbc';
    protected $table = 'DBA.pw_carrito_web';

    // ── Propiedades del carrito ─────────────────────────
    public $id_item_web;
    public $codigo_item;
    public $nombre;
    public $costo_real;
    public $pvp3;
    public $cantidad;
    public $imagen;
    public $estatus;
    public $cod_cliente;
    public $iva;
    public $presentacion;

    // ── Obtener carrito por usuario ─────────────────────
    public function getCarritoByUser(string $userId): array
    {
        $rows = DB::connection($this->connection)->select("
            SELECT * FROM {$this->table} WHERE cod_cliente = ?
        ", [$userId]);

        return array_map(fn($row) => $this->mapRowToInstance($row), $rows);
    }

    // ── Agregar producto ────────────────────────────────
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
            $data['imagen']       ?? null,
            $data['cod_cliente'],
            $data['iva']          ?? null,
            $data['presentacion'] ?? null,
        ]);
    }

    // ── Actualizar cantidad por usuario y producto ──────
    public function updateProducto(string $userId, int $productoId, int $cantidad): int
    {
        return DB::connection($this->connection)->update("
            UPDATE {$this->table}
            SET cantidad = ?
            WHERE cod_cliente = ? AND codigo_item = ?
        ", [$cantidad, $userId, $productoId]);
    }

    // ── Actualizar cantidad por id_item_web ─────────────
    public function updateCantidad(int $idItemWeb, int $cantidad): int
    {
        return DB::connection($this->connection)->update("
            UPDATE {$this->table}
            SET cantidad = ?
            WHERE id_item_web = ?
        ", [$cantidad, $idItemWeb]);
    }

    // ── Eliminar producto ───────────────────────────────
    public function removeProducto(int $idItemWeb): int
    {
        return DB::connection($this->connection)->delete("
            DELETE FROM {$this->table} WHERE id_item_web = ?
        ", [$idItemWeb]);
    }

    // ── Mapear fila a objeto ────────────────────────────
    private function mapRowToInstance($row)
    {
        $instance = new self();
        $instance->id_item_web  = $row->id_item_web;
        $instance->codigo_item  = $row->codigo_item;
        $instance->nombre       = ProductsModel::cleanString($row->nombre ?? null);
        $instance->costo_real   = $row->costo_real;
        $instance->pvp3         = $row->pvp3;
        $instance->cantidad     = $row->cantidad;
        $instance->imagen       = $row->imagen;
        $instance->estatus      = $row->estatus;
        $instance->cod_cliente  = $row->cod_cliente;
        $instance->iva          = $row->iva;
        $instance->presentacion = ProductsModel::cleanString($row->presentacion ?? null);

        return $instance;
    }
}
