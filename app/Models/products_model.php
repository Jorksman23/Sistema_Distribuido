<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class products_model extends Model
{
    protected $connection = 'odbc';
    protected $table = 'DBA.in_item';
    protected $primaryKey = 'codigo';
    public $timestamps = false;

    protected $fillable = [
        'codigo', 'descripcion1', 'linea', 'pvp1', 'pvp2', 'pvp3',
        'costo', 'iva', 'imagen', 'empresa', 'observacion', 'activo', 'stock',
    ];

    protected $casts = [
        'pvp1' => 'float',
        'pvp2' => 'float',
        'pvp3' => 'float',
        'costo' => 'float',
        'stock' => 'integer',
    ];

    /**
     * Obtiene la URL base del servidor de imágenes + RUC de la empresa
     */
    public static function getImageServerBaseUrl(string $empresa = '001'): string
    {
        $cacheKey = "image_server_{$empresa}";

        return cache()->remember($cacheKey, now()->addMinutes(30), function () use ($empresa) {
            try {
                $row = DB::connection('odbc')
                    ->selectOne(
                        "SELECT TOP 1 detalle
                         FROM web_ge_parametros
                         WHERE codigo = 348 AND empresa = ?",
                        [$empresa]
                    );

                $baseUrl = $row?->detalle ?? config('app.image_server_fallback_url', 'http://186.101.203.76:10555/');
            } catch (\Throwable $e) {
                \Log::warning("Error leyendo parámetro 348 para empresa {$empresa}");
                $baseUrl = config('app.image_server_fallback_url', 'http://186.101.203.76:10555/');
            }

            // Obtener RUC de la empresa
            $ruc = self::getCompanyRuc($empresa);

            return rtrim($baseUrl, '/') . '/' . $ruc;
        });
    }

    /**
     * Obtiene el RUC de la empresa
     */
    public static function getCompanyRuc(string $empresa): string
    {
        return cache()->remember("ruc_{$empresa}", now()->addHours(6), function () use ($empresa) {
            try {
                $row = DB::connection('odbc')
                    ->selectOne(
                        "SELECT TOP 1 ruc FROM GE_EMPRESA WHERE codigo = ?",
                        [$empresa]
                    );

                return $row?->ruc ?: $empresa;
            } catch (\Throwable $e) {
                return $empresa;
            }
        });
    }

    /**
     * URL completa de la imagen del producto
     */
    public static function getProductImageUrl(?string $filename, string $empresa = '001'): ?string
    {
        if (empty($filename)) {
            return null;
        }

        $base = self::getImageServerBaseUrl($empresa);

        return rtrim($base, '/') . '/producto/' . ltrim($filename, '/');
    }

    /**
     * Obtiene productos activos con URL de imagen
     */
    public static function getActiveProducts(int $limit = 20, string $empresa = '001'): array
    {
        $items = DB::connection('odbc')
            ->select("
                SELECT TOP {$limit}
                    i.codigo AS id,
                    i.descripcion1 AS nombre,
                    i.pvp1 AS precio,
                    i.imagen,
                    i.empresa,
                    i.stock,
                    l.linea AS categoria
                FROM DBA.in_item i
                INNER JOIN DBA.in_linea l ON i.linea = l.codigo
                WHERE i.activo = 'S'
                  AND i.empresa = ?
                ORDER BY i.codigo
            ", [$empresa]);

        return array_map(function ($item) use ($empresa) {
            $item = (array) $item;

            // UTF-8
            foreach ($item as $key => $value) {
                if (is_string($value)) {
                    $item[$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
                }
            }

            if (isset($item['precio'])) {
                $item['precio'] = number_format((float)$item['precio'], 2, '.', '');
            }

            // URL completa de imagen
            $item['imagen_url'] = self::getProductImageUrl($item['imagen'] ?? null, $item['empresa'] ?? $empresa);

            return $item;
        }, $items);
    }
}
