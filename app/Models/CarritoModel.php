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
    public $ubicacion;

    const ESTATUS_ACTIVO = '1';
    const ESTATUS_PROCESANDO = '2';


    //Obtener carrito activo de un clientE
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
                c.ubicacion,
                p.nombre AS nombre_presentacion,
                p.foto   AS foto_presentacion
            FROM {$this->table} c
            LEFT JOIN DBA.in_item_presentacion p
                ON  p.codigo   = c.presentacion
                AND p.producto = c.codigo_item
                AND p.empresa  = ?
            WHERE c.cod_cliente = ?
            AND   c.estatus     = '1'
            ORDER BY c.id_item_web DESC
        ", [
            currentCompany(),
            $codCliente
        ]);

        return array_map(fn($row) => $this->mapRowToInstance($row), $rows);
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
            AND   estatus      = '1'
        ", [$codCliente, $codigoItem, $presentacion]);

        return $row ? $this->mapRowToInstance($row) : null;
    }
    // ── Obtener item por id ──────────────────────────────
    public function getItemById(int $idItemWeb, string $codCliente): ?self
    {
        $row = DB::connection($this->connection)->selectOne("
            SELECT TOP 1 *
            FROM {$this->table}
            WHERE id_item_web = ?
            AND   cod_cliente = ?
            AND   estatus     = '1'
        ", [$idItemWeb, $codCliente]);

        return $row ? $this->mapRowToInstance($row) : null;
    }

    public function getStockDisponible(string $codigoItem, int $presentacion, string $empresa, string $ubicacion = null): float
    {
        if ($presentacion > 0) {
            $row = DB::connection($this->connection)->selectOne("
                SELECT COALESCE(SUM(cantidad), 0) AS total
                FROM DBA.in_existencia_presentacion
                WHERE item_presentacion = ?
                AND empresa = ?
                " . ($ubicacion ? "AND ubicacion = ?" : "") . "
            ", $ubicacion ? [$presentacion, $empresa, $ubicacion] : [$presentacion, $empresa]);
        } else {
            $row = DB::connection($this->connection)->selectOne("
                SELECT COALESCE(SUM(existencia), 0) AS total
                FROM DBA.in_existencia
                WHERE producto = ?
                AND empresa = ?
                " . ($ubicacion ? "AND ubicacion = ?" : "") . "
            ", $ubicacion ? [$codigoItem, $empresa, $ubicacion] : [$codigoItem, $empresa]);
        }

        return (float) ($row->total ?? 0);
    }

    // ── Agregar producto ─────────────────────────────────
    public function add(array $data): bool{
        return DB::connection($this->connection)->insert("
            INSERT INTO {$this->table}
            (codigo_item, nombre, costo_real, pvp3, cantidad, cod_cliente, imagen, estatus, iva, presentacion, ubicacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, '1', ?, ?, ?)
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
            $data['ubicacion']    ?? null,
        ]);
    }

    // Mapear fila a objeto
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
        $instance->ubicacion = $row->ubicacion ?? null;
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
