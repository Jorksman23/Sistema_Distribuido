<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class ProductsModel
{
    protected $connection = 'odbc';

    // ── Propiedades del producto ─────────────────────────
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

    // ── Buscar producto por código ───────────────────────
    public function findByCodigo($codigo, $empresa = null)
    {
        $empresa = $empresa ?? currentCompany();

        $row = DB::connection($this->connection)->selectOne("
            SELECT TOP 1 *
            FROM DBA.in_item
            WHERE codigo = ? AND empresa = ? AND activo = 'S'
        ", [$codigo, $empresa]);

        if (!$row) return null;

        return $this->mapRowToInstance($row);
    }

    // ── Crear producto ───────────────────────────────────
    public function createProduct($data)
    {
        return DB::connection($this->connection)->insert("
            INSERT INTO DBA.in_item
            (codigo, empresa, descripcion1, linea, pvp1, pvp2, pvp3, costo, iva, imagen, observacion, activo, stock)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $data['codigo'],
            $data['empresa'] ?? currentCompany(),
            $data['descripcion1'],
            $data['linea'],
            $data['pvp1'],
            $data['pvp2'],
            $data['pvp3'],
            $data['costo'],
            $data['iva'],
            $data['imagen'] ?? null,
            $data['observacion'] ?? null,
            $data['activo'] ?? 'S',
            $data['stock'] ?? 0,
        ]);
    }

    // ── Listar productos activos ─────────────────────────
    public function getActiveProducts(int $limit = 50, string $empresa = null): array
    {
        $empresa = $empresa ?? currentCompany();

        $rows = DB::connection($this->connection)->select("
            SELECT TOP {$limit}
                i.codigo,
                i.empresa,
                i.descripcion1,
                i.pvp1,
                i.imagen,
                i.stock,
                l.linea AS categoria
            FROM DBA.in_item i
            LEFT JOIN DBA.in_linea l
                ON i.linea = l.codigo AND l.empresa = i.empresa
            WHERE i.activo = 'S' AND i.empresa = ?
            ORDER BY i.codigo
        ", [$empresa]);

        return array_map(fn($row) => $this->mapRowToInstance($row), $rows);
    }

    // ── Actualizar producto ──────────────────────────────
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

    // ── Mapear fila a objeto ─────────────────────────────
    private function mapRowToInstance($row)
    {
        $instance = new self();
        $instance->codigo       = $row->codigo;
        $instance->empresa      = $row->empresa;
        $instance->descripcion1 = self::cleanString($row->descripcion1);
        $instance->pvp1         = number_format((float)$row->pvp1, 2, '.', '');
        $instance->imagen       = $row->imagen;
        $instance->stock        = $row->stock;
        $instance->categoria    = self::cleanString($row->categoria ?? null);
        $instance->imagen_url   = productImageUrl($row->imagen);

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

    //Paginado de Productos
    public function getPaginatedProducts(int $page = 1, int $perPage = 12, string $empresa = null): array{
        $empresa  = $empresa ?? currentCompany();
        // SQL Anywhere usa START AT (base 1, no base 0)
        $startAt  = (($page - 1) * $perPage) + 1;

        // Total de productos
        $totalRow = DB::connection($this->connection)->selectOne("
            SELECT COUNT(*) AS total
            FROM DBA.in_item i
            WHERE i.activo = 'S' AND i.empresa = ?
        ", [$empresa]);

        $total = $totalRow->total ?? 0;

        // Paginación con TOP ... START AT (sintaxis SQL Anywhere)
        $rows = DB::connection($this->connection)->select("
            SELECT TOP {$perPage} START AT {$startAt}
                i.codigo,
                i.empresa,
                i.descripcion1,
                i.pvp1,
                i.imagen,
                i.stock,
                l.linea AS categoria
            FROM DBA.in_item i
            LEFT JOIN DBA.in_linea l
                ON i.linea = l.codigo AND l.empresa = i.empresa
            WHERE i.activo = 'S' AND i.empresa = ?
            ORDER BY i.codigo
        ", [$empresa]);

        $productos = array_map(fn($row) => $this->mapRowToInstance($row), $rows);

        return [
            'data'         => $productos,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }
}


// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Cache;
// use Illuminate\Support\Facades\Log;

// class products_model extends Model
// {
//     protected $connection = 'odbc';
//     protected $table = 'DBA.in_item';
//     protected $primaryKey = 'codigo';
//     public $timestamps = false;

//     protected $fillable = [
//         'codigo', 'empresa', 'descripcion1', 'linea', 'pvp1', 'pvp2', 'pvp3',
//         'costo', 'iva', 'imagen', 'observacion', 'activo', 'stock',
//     ];

//     protected $casts = [
//         'pvp1' => 'float',
//         'pvp2' => 'float',
//         'pvp3' => 'float',
//         'costo' => 'float',
//         'stock' => 'integer',
//     ];


//     public static function getProductImageUrl(?string $filename, string $empresa = '005'): ?string
//     {
//         if (empty($filename)) {
//             return null;
//         }

//         $base = self::getImageServerUrl($empresa);
//         return rtrim($base, '/') . '/product/' . ltrim($filename, '/');
//     }

//     public static function getImageServerUrl(string $empresa = '005'): string
//     {
//         $fallback = 'http://186.101.203.76:10555/';

//         try {
//             $row = DB::connection('odbc')
//                 ->selectOne("SELECT TOP 1 detalle FROM web_ge_parametros WHERE codigo = 348 AND empresa = ?", [$empresa]);

//             $detalle = $row?->detalle ?? '';
//             $baseUrl = (empty($detalle) || str_contains($detalle, 'Servidor')) ? $fallback : $detalle;
//         } catch (\Throwable $e) {
//             $baseUrl = $fallback;
//         }

//         $ruc = self::getCompanyRuc($empresa);
//         return rtrim($baseUrl, '/') . '/' . $ruc;
//     }

//     public static function getCompanyRuc(string $empresa): string
//     {
//         return Cache::remember("ruc_{$empresa}", now()->addHours(6), function () use ($empresa) {
//             $row = DB::connection('odbc')->selectOne("SELECT TOP 1 ruc FROM GE_EMPRESA WHERE codigo = ?", [$empresa]);
//             return $row?->ruc ?: $empresa;
//         });
//     }


//     public static function getActiveProducts(int $limit = 50): array
//     {
//         $empresa = '005';

//         // Obtener productos
//         $items = DB::connection('odbc')
//             ->select("
//                 SELECT DISTINCT TOP {$limit}
//                     i.codigo AS id,
//                     i.descripcion1 AS nombre,
//                     i.pvp1 AS precio,
//                     i.imagen AS imagen_principal,
//                     i.stock,
//                     l.linea AS categoria
//                 FROM DBA.in_item i
//                 LEFT JOIN DBA.in_linea l ON i.linea = l.codigo
//                 WHERE i.activo = 'S'
//                   AND i.empresa = ?
//                 ORDER BY i.codigo
//             ", [$empresa]);

//         if (empty($items)) {
//             return [];
//         }

//         $productIds = array_column($items, 'id');

//         $presentationsRaw = DB::connection('odbc')
//             ->select("
//                 SELECT producto, foto, nombre
//                 FROM in_item_presentacion
//                 WHERE producto IN (" . str_repeat('?,', count($productIds) - 1) . "?)
//                   AND mostrar = 'S'
//             ", $productIds);

//         // Agrupar por producto
//         $presentations = [];
//         foreach ($presentationsRaw as $p) {
//             $presentations[$p->producto][] = $p;
//         }

//         return array_map(function ($item) use ($empresa, $presentations) {
//             $item = (array) $item;

            // // Limpieza UTF-8
            // foreach ($item as $key => $value) {
            //     if (is_string($value) && $value !== '') {
            //         $value = str_replace(['�', "\r", "\n", "\t"], ' ', $value);
            //         $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
            //         if ($converted === false) {
            //             $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);
            //         }
            //         $item[$key] = $converted !== false ? trim($converted) : trim($value);
            //     }
            // }

            // if (isset($item['precio'])) {
            //     $item['precio'] = number_format((float)$item['precio'], 2, '.', '');
            // }

            // $item['imagen_url'] = self::getProductImageUrl($item['imagen_principal'] ?? null, $empresa);

            // // Imágenes de la tabla presentacion
            // $item['imagenes'] = [];
            // if (isset($presentations[$item['id']]) && count($presentations[$item['id']]) > 0) {
            //     $item['imagenes'] = array_map(function ($p) use ($empresa) {
            //         return [
            //             'url'    => self::getProductImageUrl(trim($p->foto ?? ''), $empresa),
            //             'nombre' => trim($p->nombre ?? '')
            //         ];
            //     }, $presentations[$item['id']]);
            // }

//             return $item;
//         }, $items);
//     }
// }
