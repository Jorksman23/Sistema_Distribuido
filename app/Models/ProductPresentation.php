<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\ProductsModel;

class ProductPresentation
{
    protected $connection = 'odbc';
    protected $table = 'DBA.in_item_presentacion';

    public $producto;
    public $nombre;
    public $foto;
    public $foto_url;

    /**
     * Obtener producto + presentaciones
     */
    public function getByProduct(string $codigoProducto, string $empresa = null, int $limit = 8): array
    {
        $empresa = $empresa ?? currentCompany();

        // Buscar el producto principal
        $producto = (new ProductsModel())->findByCodigo($codigoProducto, $empresa);
        if (!$producto) {
            return [];
        }

        // Buscar presentaciones
        $rows = DB::connection($this->connection)->select("
            SELECT TOP {$limit}
                producto,
                nombre,
                foto
            FROM {$this->table}
            WHERE empresa = ?
              AND producto = ?
              AND mostrar = 'S'
            ORDER BY nombre
        ", [$empresa, $codigoProducto]);

        $presentaciones = array_map(
            fn($row) => $this->mapRowToInstance($row, $codigoProducto),
            $rows
        );

        // Respuesta final
        return [
            'codigo'        => $producto->codigo,
            'empresa'       => $producto->empresa,
            'descripcion'   => $producto->descripcion1,
            'precio'        => $producto->pvp1,
            'imagen'        => $producto->imagen,
            'imagen_url'    => $producto->imagen_url,
            'stock'         => $producto->stock,
            'categoria'     => $producto->categoria,
            'presentaciones'=> $presentaciones,
        ];
    }

    /**
     * Mapea cada presentación
     */
    private function mapRowToInstance($row, string $codigoProducto)
    {
        $instance = new self();
        $instance->producto  = $row->producto;
        $instance->nombre    = self::cleanString($row->nombre);
        $instance->foto      = $row->foto;
        $instance->foto_url  = presentationImageUrl($row->foto, $codigoProducto); // ← Clave aquí
        return $instance;
    }

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
