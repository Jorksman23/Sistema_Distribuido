<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class CarritoModel
{
    protected $connection = 'odbc';
    protected $table      = 'DBA.pw_carrito_web';

    public $id_item_web;
    public $codigo_item;
    public $nombre;
    public $costo_real;
    public $pvp3;
    public $cantidad;
    public $cod_cliente;
    public $imagen;
    public $imagen_url;
    public $estatus;
    public $iva;
    public $presentacion;
    public $nombre_presentacion;

    // ── Obtener carrito activo de un cliente ─────────────
    public function getCarritoByUser(string $codCliente): array
    {
        $rows = DB::connection($this->connection)->select("
            SELECT
                c.id_item_web,
                c.codigo_item,
                c.nombre,
                c.costo_real,
                c.pvp3,
                c.cantidad,
                c.cod_cliente,
                c.imagen,
                c.estatus,
                c.iva,
                c.presentacion,
                p.nombre AS nombre_presentacion,
                p.foto   AS foto_presentacion
            FROM {$this->table} c
            LEFT JOIN DBA.in_item_presentacion p
                ON  p.codigo  = c.presentacion
                AND p.producto = c.codigo_item
            WHERE c.cod_cliente = ?
            AND   c.estatus     = 'A'
            ORDER BY c.id_item_web DESC
        ", [$codCliente]);

        return array_map(fn($row) => $this->mapRowToInstance($row), $rows);
    }

    // ── Verificar si un producto ya está en el carrito ───
    public function exists(string $codCliente, string $codigoItem, int $presentacion = 0): bool
    {
        $row = DB::connection($this->connection)->selectOne("
            SELECT TOP 1 id_item_web
            FROM {$this->table}
            WHERE cod_cliente = ?
            AND   codigo_item = ?
            AND   presentacion = ?
            AND   estatus     = 'A'
        ", [$codCliente, $codigoItem, $presentacion]);

        return $row !== null;
    }

    // ── Obtener item específico ──────────────────────────
    public function getItemByProducto(string $codCliente, string $codigoItem, int $presentacion = 0): ?self
    {
        $row = DB::connection($this->connection)->selectOne("
            SELECT TOP 1 *
            FROM {$this->table}
            WHERE cod_cliente  = ?
            AND   codigo_item  = ?
            AND   presentacion = ?
            AND   estatus      = 'A'
        ", [$codCliente, $codigoItem, $presentacion]);

        return $row ? $this->mapRowToInstance($row) : null;
    }

    // ── Agregar producto ─────────────────────────────────
    public function add(array $data): bool
    {
        return DB::connection($this->connection)->insert("
            INSERT INTO {$this->table}
            (codigo_item, nombre, costo_real, pvp3, cantidad, cod_cliente, imagen, estatus, iva, presentacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'A', ?, ?)
        ", [
            $data['codigo_item'],
            $data['nombre'],
            $data['costo_real'] ?? $data['pvp3'],
            $data['pvp3'],
            $data['cantidad']     ?? 1,
            $data['cod_cliente'],
            $data['imagen']       ?? null,
            $data['iva']          ?? 'N',
            $data['presentacion'] ?? 0,
        ]);
    }

    // ── Actualizar cantidad ──────────────────────────────
    public function updateCantidad(int $idItemWeb, string $codCliente, int $cantidad): int
    {
        return DB::connection($this->connection)->update("
            UPDATE {$this->table}
            SET cantidad  = ?
            WHERE id_item_web = ?
            AND   cod_cliente = ?
            AND   estatus     = 'A'
        ", [$cantidad, $idItemWeb, $codCliente]);
    }

    // ── Eliminar producto ────────────────────────────────
    public function remove(int $idItemWeb, string $codCliente): int
    {
        return DB::connection($this->connection)->delete("
            DELETE FROM {$this->table}
            WHERE id_item_web = ?
            AND   cod_cliente = ?
        ", [$idItemWeb, $codCliente]);
    }

    // ── Vaciar carrito ───────────────────────────────────
    public function vaciar(string $codCliente): int
    {
        return DB::connection($this->connection)->delete("
            DELETE FROM {$this->table}
            WHERE cod_cliente = ?
            AND   estatus     = 'A'
        ", [$codCliente]);
    }

    // ── Contar items ─────────────────────────────────────
    public function count(string $codCliente): int
    {
        $row = DB::connection($this->connection)->selectOne("
            SELECT COUNT(*) AS total
            FROM {$this->table}
            WHERE cod_cliente = ?
            AND   estatus     = 'A'
        ", [$codCliente]);

        return (int) ($row->total ?? 0);
    }

    // ── Calcular total ───────────────────────────────────
    public function getTotal(string $codCliente): float
    {
        $row = DB::connection($this->connection)->selectOne("
            SELECT SUM(
                CAST(pvp3 AS NUMERIC(15,6)) *
                CAST(cantidad AS INTEGER)
            ) AS total
            FROM {$this->table}
            WHERE cod_cliente = ?
            AND   estatus     = 'A'
        ", [$codCliente]);

        return (float) ($row->total ?? 0);
    }

    // ── Mapear fila a objeto ─────────────────────────────
    private function mapRowToInstance($row): self
    {
        $instance                    = new self();
        $instance->id_item_web       = $row->id_item_web;
        $instance->codigo_item       = $row->codigo_item;
        $instance->nombre            = ProductsModel::cleanString($row->nombre ?? null);
        $instance->costo_real        = number_format((float)($row->costo_real ?? 0), 2, '.', '');
        $instance->pvp3              = number_format((float)($row->pvp3 ?? 0), 2, '.', '');
        $instance->cantidad          = (int)($row->cantidad ?? 1);
        $instance->cod_cliente       = $row->cod_cliente;
        $instance->imagen            = $row->imagen;
        $instance->estatus           = $row->estatus;
        $instance->iva               = $row->iva ?? 'N';
        $instance->presentacion      = (int)($row->presentacion ?? 0);
        $instance->nombre_presentacion = ProductsModel::cleanString($row->nombre_presentacion ?? null);

        // Si tiene presentacion usa la foto de la variante
        // Si no usa la imagen principal del producto
        if ($instance->presentacion > 0 && !empty($row->foto_presentacion)) {
            $instance->imagen_url = presentationImageUrl(
                $row->foto_presentacion,
                $row->codigo_item
            );
        } else {
            $instance->imagen_url = productImageUrl($row->imagen);
        }

        return $instance;
    }
}
