<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\ProductsModel;
class ProductPresentation
{
    protected $connection = 'odbc';
    protected $table = 'DBA.in_item_presentacion';

    // Propiedades públicas
    public $codigo;
    public $producto;
    public $nombre;
    public $foto;
    public $foto_url;
    public $stock_presentacion;

    /**
 * Obtener producto + presentaciones.
 * Si hay ubicación de sesión (usuario logueado), filtra stock únicamente por esa ubicación.
 * Si no hay ubicación de sesión (visitante invitado), suma todas las ubicaciones
 * visibles en la tienda (view_on_tienda = 'S'), igual que el comportamiento del catálogo público.
 */
public function getByProduct(string $codigoProducto, string $empresa = null, ?int $limit = null, string $ubicacion = ''): array{
    $empresa   = $empresa ?? currentCompany();
    $ubicacion = $ubicacion !== '' ? $ubicacion : (string) session('ubicacion_seleccionada', '');

    // Obtener producto base desde el repositorio
    $raw = (new \App\Repositories\ProductRepository())->findByCodigo($codigoProducto, $empresa);
    if (!$raw) {
        return [];
    }
    $producto = (new ProductsModel())->mapRowToInstance($raw);

    // Si hay ubicación de sesión, el JOIN filtra solo esa ubicación.
    // Si no hay (invitado), filtra por todas las ubicaciones visibles (view_on_tienda = 'S').
    $joinParams = [];
    if ($ubicacion !== '') {
        $stockJoinCondition = "AND e.ubicacion = ?";
        $joinParams[] = $ubicacion;
    } else {
        $stockJoinCondition = "
            AND e.ubicacion IN (
                SELECT codigo FROM DBA.in_ubicacion
                WHERE empresa = p.empresa
                AND view_on_tienda = 'S'
            )";
    }

    $sql = "
        SELECT " . (!empty($limit) ? "TOP {$limit}" : "") . "
            p.producto,
            p.codigo,
            p.nombre,
            p.foto,
            COALESCE(SUM(e.cantidad), 0) AS stock_presentacion
        FROM {$this->table} p
        LEFT JOIN DBA.in_existencia_presentacion e
            ON e.item_presentacion = p.codigo
            AND e.empresa = p.empresa
            {$stockJoinCondition}
        WHERE p.empresa = ?
        AND p.producto = ?
        AND p.mostrar = 'S'
        GROUP BY p.producto, p.codigo, p.nombre, p.foto
        HAVING COALESCE(SUM(e.cantidad), 0) > 0
        ORDER BY p.nombre
    ";

    $rows = DB::connection($this->connection)->select($sql, array_merge($joinParams, [$empresa, $codigoProducto]));

    $presentaciones = array_map(
        fn($row) => $this->mapRowToInstance($row, $codigoProducto),
        $rows
    );

    // Stock total: mismo criterio dual aplicado al cálculo del total.
    if ($ubicacion !== '') {
        if (!empty($presentaciones)) {
            $stockRow = DB::connection($this->connection)->selectOne("
                SELECT COALESCE(SUM(ep.cantidad), 0) AS total
                FROM DBA.in_existencia_presentacion ep
                INNER JOIN DBA.in_item_presentacion p
                    ON p.codigo = ep.item_presentacion
                    AND p.empresa = ep.empresa
                WHERE p.producto = ? AND p.empresa = ?
                AND ep.ubicacion = ?
            ", [$codigoProducto, $empresa, $ubicacion]);
        } else {
            $stockRow = DB::connection($this->connection)->selectOne("
                SELECT COALESCE(SUM(e.existencia), 0) AS total
                FROM DBA.in_existencia e
                WHERE e.producto = ? AND e.empresa = ?
                AND e.ubicacion = ?
            ", [$codigoProducto, $empresa, $ubicacion]);
        }
    } else {
        if (!empty($presentaciones)) {
            $stockRow = DB::connection($this->connection)->selectOne("
                SELECT COALESCE(SUM(ep.cantidad), 0) AS total
                FROM DBA.in_existencia_presentacion ep
                INNER JOIN DBA.in_item_presentacion p
                    ON p.codigo = ep.item_presentacion
                    AND p.empresa = ep.empresa
                INNER JOIN DBA.in_ubicacion u
                    ON u.codigo = ep.ubicacion
                    AND u.empresa = ep.empresa
                WHERE p.producto = ? AND p.empresa = ?
                AND u.view_on_tienda = 'S'
            ", [$codigoProducto, $empresa]);
        } else {
            $stockRow = DB::connection($this->connection)->selectOne("
                SELECT COALESCE(SUM(e.existencia), 0) AS total
                FROM DBA.in_existencia e
                INNER JOIN DBA.in_ubicacion u
                    ON u.codigo = e.ubicacion
                    AND u.empresa = e.empresa
                WHERE e.producto = ? AND e.empresa = ?
                AND u.view_on_tienda = 'S'
            ", [$codigoProducto, $empresa]);
        }
    }
    $stockTotal = (float) ($stockRow->total ?? 0);

    return [
        'codigo'         => $producto->codigo,
        'empresa'        => $producto->empresa,
        'descripcion'    => $producto->descripcion1,
        'precio'         => $producto->pvp1,
        'imagen'         => $producto->imagen,
        'imagen_url'     => $producto->imagen_url,
        'stock'          => $producto->stock,
        'stock_total'    => $stockTotal,
        'categoria'      => $producto->categoria,
        'grupo'          => $producto->grupo,
        'presentaciones' => $presentaciones,
    ];
}

    /**
     * Mapear fila de presentación a objeto
     */
    private function mapRowToInstance($row, string $codigoProducto): self
    {
        $instance = new self();
        $instance->codigo    = $row->codigo;
        $instance->producto  = $row->producto;
        $instance->nombre    = self::cleanString($row->nombre);
        $instance->foto      = $row->foto;
        $instance->foto_url  = presentationImageUrl($row->foto, $codigoProducto);
        $instance->stock_presentacion = (float) $row->stock_presentacion;
        return $instance;
    }

    /**
     * Limpieza de cadenas
     */
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
