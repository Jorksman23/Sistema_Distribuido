<?php

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




namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class products_model extends Model
{
    protected $connection = 'odbc';
    protected $table = 'DBA.in_item';
    protected $primaryKey = 'codigo';
    public $timestamps = false;

    protected $fillable = [
        'codigo', 'empresa', 'descripcion1', 'linea', 'pvp1', 'pvp2', 'pvp3',
        'costo', 'iva', 'imagen', 'observacion', 'activo', 'stock',
    ];

    protected $casts = [
        'pvp1'   => 'float',
        'pvp2'   => 'float',
        'pvp3'   => 'float',
        'costo'  => 'float',
        'stock'  => 'integer',
    ];

    /**
     * Genera la URL de la imagen principal validando extensión.
     */
    public static function getProductImageUrl(?string $filename, string $empresa = null): ?string
    {
        if (empty($filename)) {
            return null;
        }

        $empresa = $empresa ?? currentCompany();
        $baseUrl = companyImageBaseUrl();

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions)) {
            Log::warning("Formato de imagen no permitido: {$filename}");
            return null;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($filename, '/');
    }

    /**
     * Limpieza segura de cadenas para evitar errores de codificación.
     */
    public static function cleanString(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $value = str_replace(['�', "\r", "\n", "\t"], ' ', $value);
        $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        if ($converted === false) {
            $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);
        }

        return $converted !== false ? trim($converted) : trim($value);
    }

    /**
     * Devuelve productos activos con su imagen principal.
     */
    public static function getActiveProducts(int $limit = 50, string $empresa = null): array
    {
        $empresa = $empresa ?? currentCompany();

        $items = DB::connection('odbc')->select("
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
            WHERE i.activo = 'S'
              AND i.empresa = ?
            ORDER BY i.codigo
        ", [$empresa]);

        return array_map(function ($item) use ($empresa) {
            return [
                'codigo'       => $item->codigo,
                'descripcion1' => self::cleanString($item->descripcion1),
                'pvp1'         => number_format((float)$item->pvp1, 2, '.', ''),
                'empresa'      => self::cleanString($item->empresa),
                'stock'        => $item->stock,
                'categoria'    => self::cleanString($item->categoria),
                'imagen_url'   => self::getProductImageUrl($item->imagen, $empresa),
            ];
        }, $items);
    }
}
