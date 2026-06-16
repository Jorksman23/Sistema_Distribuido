<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class ProductsModel
{
    protected $connection = 'odbc';
    //Propiedades del producto
    public $codigo;
    public $empresa;
    public $descripcion1;
    public $linea;
    public $pvp1;
    public $pvp2;
    public $pvp3;
    public $costo;
    public $iva;
    public $imagen;
    public $observacion;
    public $activo;
    public $stock;
    public $categoria;
    public $imagen_url;
    public $stock_total;
    public $tiene_presentaciones;
    public $grupo;

    // ── Buscar producto por código ───────────────────────
    // public function findByCodigo($codigo, $empresa = null)
    // {
    //     $empresa = $empresa ?? currentCompany();

    //     $row = DB::connection($this->connection)->selectOne("
    //         SELECT TOP 1 *
    //         FROM DBA.in_item
    //         WHERE codigo = ? AND empresa = ? AND stock in ('S', 'N')
    //     ", [$codigo, $empresa]);

    //     if (!$row) return null;

    //     return $this->mapRowToInstance($row);
    // }

    //Mapear fila a objeto
    public function mapRowToInstance($row): self
    {
        $instance = new self();
        $instance->codigo                = $row->codigo;
        $instance->empresa               = $row->empresa;
        $instance->descripcion1          = self::cleanString($row->descripcion1);
        $instance->pvp1                  = number_format((float)$row->pvp1, 2, '.', '');
        $instance->iva                   = $row->iva ?? 'N';
        $instance->imagen                = $row->imagen;
        $instance->stock                 = $row->stock;
        $instance->grupo                 = $row->grupo ?? null;
        $instance->categoria             = self::cleanString($row->categoria ?? null);
        $instance->imagen_url            = productImageUrl($row->imagen);
        $instance->stock_total           = (float) ($row->stock_total ?? 0);
        $instance->tiene_presentaciones  = (bool) ($row->tiene_presentaciones ?? false);
        return $instance;
    }

    //Limpieza de cadenas
    public static function cleanString(?string $value): ?string
    {
        if ($value === null || $value === '') return $value;
        $value     = str_replace(['?', "\r", "\n", "\t"], ' ', $value);
        $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        if ($converted === false) {
            $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);
        }
        return $converted !== false ? trim($converted) : trim($value);
    }

    //  Actualizar producto
    public function updateProduct($codigo, $empresa, $data)
    {
        return DB::connection($this->connection)->update("
            UPDATE DBA.in_item
            SET descripcion1 = ?, pvp1 = ?, stock = ?
            WHERE codigo = ? AND empresa = ?
        ", [
            $data['descripcion1'],
            $data['pvp1'],
            $data['stock'],
            $codigo,
            $empresa
        ]);
    }
}
