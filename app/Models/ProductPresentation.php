<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class ProductPresentation
{
    protected $connection = 'odbc';

    // ── Propiedades de la presentación ───────────────────
    public $codigo;
    public $empresa;
    public $producto;
    public $nombre;
    public $foto;
    public $foto_id;
    public $mostrar;
    public $foto_url;

    // ── Obtener todas las presentaciones activas ─────────
    public function getPresentations(string $empresa = null): array
    {
        $empresa = $empresa ?? currentCompany();

        $rows = DB::connection($this->connection)->select("
            SELECT producto, nombre, foto
            FROM in_item_presentacion
            WHERE empresa = ?
              AND mostrar = 'S'
        ", [$empresa]);

        return array_map(fn($row) => $this->mapRowToInstance($row, $empresa), $rows);
    }

    // ── Obtener presentaciones de un producto específico ─
    public function getByProduct(int $limit = 50,string $codigoProducto, string $empresa = null): array
    {
        $empresa = $empresa ?? currentCompany();

        $rows = DB::connection($this->connection)->select("
            SELECT producto, nombre, foto
            FROM in_item_presentacion
            WHERE empresa = ?
              AND producto = ?
              AND mostrar = 'S'
        ", [$empresa, $codigoProducto]);

        return array_map(fn($row) => $this->mapRowToInstance($row, $empresa), $rows);
    }

    // ── Mapear fila a objeto ─────────────────────────────
    private function mapRowToInstance($row, string $empresa)
    {
        $instance = new self();
        $instance->producto  = $row->producto;
        $instance->nombre    = self::cleanString($row->nombre);
        $instance->foto      = $row->foto;
        $instance->foto_url  = productImageUrl($row->foto);

        return $instance;
    }

    // ── Limpieza de cadenas ──────────────────────────────
    public static function cleanString(?string $value): ?string
    {
        if ($value === null || $value === '') return $value;

        $value = str_replace(['�', "\r", "\n", "\t"], ' ', $value);
        $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        if ($converted === false) {
            $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);
        }

        return $converted !== false ? trim($converted) : trim($value);
    }
}
