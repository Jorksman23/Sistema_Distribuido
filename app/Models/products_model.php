<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
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
        'pvp1' => 'float',
        'pvp2' => 'float',
        'pvp3' => 'float',
        'costo' => 'float',
        'stock' => 'integer',
    ];

    // Relación con presentaciones (variantes de color/imagen)
    public function presentations()
    {
        return $this->hasMany(ProductPresentation::class, 'producto', 'codigo')
                    ->where('mostrar', 'S')
                    ->orderBy('foto_id');
    }

        public static function getProductImageUrl(?string $filename, string $empresa = '001'): ?string
    {
        if (empty($filename)) {
            return null;
        }

        $base = self::getImageServerUrl($empresa);
        return rtrim($base, '/') . '/product/' . ltrim($filename, '/');
    }

        /**
     * Obtiene la URL base del servidor de imágenes
     */
      public static function getImageServerUrl(string $empresa = '005'): string
    {
        $fallback = 'http://186.101.203.76:10555/';

        try {
            $row = DB::connection('odbc')
                ->selectOne(
                    "SELECT TOP 1 detalle FROM web_ge_parametros WHERE codigo = 348 AND empresa = ?",
                    [$empresa]
                );

            $detalle = $row?->detalle ?? '';

            // Si el detalle no parece una URL válida, usamos el fallback
            if (empty($detalle) || str_contains($detalle, 'Servidor de imagenes') || !str_starts_with($detalle, 'http')) {
                $baseUrl = $fallback;
            } else {
                $baseUrl = $detalle;
            }
        } catch (\Throwable $e) {
            $baseUrl = $fallback;
        }

        $ruc = self::getCompanyRuc($empresa);
        return rtrim($baseUrl, '/') . '/' . $ruc;
    }
    public static function getCompanyRuc(string $empresa): string
    {
        return Cache::remember("ruc_{$empresa}", now()->addHours(6), function () use ($empresa) {
            $row = DB::connection('odbc')->selectOne("SELECT TOP 1 ruc FROM GE_EMPRESA WHERE codigo = ?", [$empresa]);
            return $row?->ruc ?: $empresa;
        });
    }


       public static function getActiveProducts(int $limit = 50): array
    {
        $empresa = '005';

        $items = DB::connection('odbc')
            ->select("
                SELECT DISTINCT TOP {$limit}
                    i.codigo AS id,
                    i.descripcion1 AS nombre,
                    i.pvp1 AS precio,
                    i.imagen AS imagen_principal,
                    i.stock,
                    l.linea AS categoria
                FROM DBA.in_item i
                LEFT JOIN DBA.in_linea l ON i.linea = l.codigo
                WHERE i.activo = 'S'
                  AND i.empresa = ?
                ORDER BY i.codigo
            ", [$empresa]);

        if (empty($items)) {
            return [];
        }

        $productIds = array_column($items, 'id');

        $presentationsRaw = DB::connection('odbc')
            ->select("
                SELECT producto, foto, nombre
                FROM in_item_presentacion
                WHERE producto IN (" . str_repeat('?,', count($productIds) - 1) . "?)
                  AND mostrar = 'S'
            ", $productIds);

        $presentations = [];
        foreach ($presentationsRaw as $p) {
            $presentations[$p->producto][] = $p;
        }

        return array_map(function ($item) use ($empresa, $presentations) {
            $item = (array) $item;

            // LIMPIEZA AGRESIVA DE CARACTERES
            foreach ($item as $key => $value) {
                if (is_string($value)) {
                    // Reemplazar caracteres inválidos
                    $value = preg_replace('/[^\x20-\x7E]/', ' ', $value); // Solo caracteres imprimibles
                    $value = str_replace(['�', 'Â', 'Ã', 'Ã¡', 'Ã©'], ' ', $value);

                    $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
                    if ($converted === false) {
                        $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);
                    }
                    $item[$key] = $converted !== false ? trim($converted) : trim($value);
                }
            }

            if (isset($item['precio'])) {
                $item['precio'] = number_format((float)$item['precio'], 2, '.', '');
            }

            $item['imagen_url'] = self::getProductImageUrl($item['imagen_principal'] ?? null, $empresa);

            $item['imagenes'] = [];
            if (isset($presentations[$item['id']])) {
                $item['imagenes'] = array_map(function ($p) use ($empresa) {
                    $foto = trim($p->foto ?? '');
                    $nombre = trim($p->nombre ?? '');

                    // Limpieza del nombre
                    if (is_string($nombre)) {
                        $nombre = preg_replace('/[^\x20-\x7E]/', ' ', $nombre);
                        $nombre = @mb_convert_encoding($nombre, 'UTF-8', 'Windows-1252') ?: $nombre;
                    }

                    return [
                        'url'    => self::getProductImageUrl($foto, $empresa),
                        'nombre' => $nombre
                    ];
                }, $presentations[$item['id']]);
            }

            return $item;
        }, $items);
    }
}
