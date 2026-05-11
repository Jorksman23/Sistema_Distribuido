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

    //Normalizador de cadenas para búsqueda
    private function normalizeString(string $text): string{
            $from = ['á','é','í','ó','ú','ü','ñ','Á','É','Í','Ó','Ú','Ü','Ñ'];
            $to   = ['a','e','i','o','u','u','n','a','e','i','o','u','u','n'];
            return str_replace($from, $to, mb_strtolower($text, 'UTF-8'));
        }

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


    //Bsuqueda de productos por descripción
    public function searchProducts(string $search, string $empresa = null): array{
            $empresa = $empresa ?? currentCompany();

            $searchOriginal   = '%' . $search . '%';
            $searchNormalizado = '%' . $this->normalizeString($search) . '%';

            $rows = DB::connection($this->connection)->select("
                SELECT
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
                AND i.descripcion1 LIKE ?
                OR i.descripcion1 LIKE ?
                ORDER BY i.codigo
            ", [$empresa, $searchOriginal, $searchNormalizado]);

            return array_map(fn($r) => $this->mapRowToInstance($r), $rows);
    }

//Trabajando en el filtrado esto es lo nuevo
    // ── Obtener Grupos ───────────────────────────────────
    public function getGrupos(string $empresa = null): array
    {
        $empresa = $empresa ?? currentCompany();
        return DB::connection($this->connection)->select("
            SELECT codigo, grupo
            FROM DBA.in_grupo
            WHERE empresa = ?
            ORDER BY grupo
        ", [$empresa]);
    }

    // ── Obtener Líneas ───────────────────────────────────
    public function getLineas(string $empresa = null): array
    {
        $empresa = $empresa ?? currentCompany();
        return DB::connection($this->connection)->select("
            SELECT codigo, linea
            FROM DBA.in_linea
            WHERE empresa = ?
            ORDER BY linea
        ", [$empresa]);
    }

    // ── Obtener Ubicaciones ──────────────────────────────
    public function getUbicaciones(string $empresa = null): array
    {
        $empresa = $empresa ?? currentCompany();
        return DB::connection($this->connection)->select("
            SELECT codigo, ubicacion
            FROM DBA.in_ubicacion
            WHERE empresa = ?
            ORDER BY ubicacion
        ", [$empresa]);
    }

    public function getPaginatedProducts(
        int    $page      = 1,
        int    $perPage   = 12,
        string $empresa   = null,
        string $search    = '',
        string $grupo     = '',
        string $linea     = '',
        string $ubicacion = '',
        float  $precioMin = 0,
        float  $precioMax = 0,
        string $orden     = 'codigo'
        ): array {
        $empresa = $empresa ?? currentCompany();
        $startAt = (($page - 1) * $perPage) + 1;

        $where  = "WHERE i.activo = 'S' AND i.empresa = ?";
        $params = [$empresa];

        if ($search !== '') {
            $normalizado = $this->normalizeString($search);
            $where      .= " AND (i.descripcion1 LIKE ? OR i.descripcion1 LIKE ?)";
            $params[]    = '%' . $search . '%';
            $params[]    = '%' . $normalizado . '%';
        }

        if ($grupo !== '') {
            $where   .= " AND i.grupo = ?";
            $params[] = $grupo;
        }

        if ($linea !== '') {
            $where   .= " AND i.linea = ?";
            $params[] = $linea;
        }

        if ($precioMin > 0) {
            $where   .= " AND i.pvp1 >= ?";
            $params[] = $precioMin;
        }

        if ($precioMax > 0) {
            $where   .= " AND i.pvp1 <= ?";
            $params[] = $precioMax;
        }

        $orderBy = match($orden) {
            'precio_asc'  => 'i.pvp1 ASC',
            'precio_desc' => 'i.pvp1 DESC',
            'nombre'      => 'i.descripcion1 ASC',
            default       => 'i.codigo ASC',
        };

        $totalRow = DB::connection($this->connection)->selectOne("
            SELECT COUNT(*) AS total
            FROM DBA.in_item i
            {$where}
        ", $params);

        $total = $totalRow->total ?? 0;

        $rows = DB::connection($this->connection)->select("
            SELECT TOP {$perPage} START AT {$startAt}
                i.codigo,
                i.empresa,
                i.descripcion1,
                i.pvp1,
                i.imagen,
                i.stock,
                i.grupo,
                l.linea AS categoria
            FROM DBA.in_item i
            LEFT JOIN DBA.in_linea l
                ON i.linea = l.codigo AND l.empresa = i.empresa
            {$where}
            ORDER BY {$orderBy}
        ", $params);

        return [
            'data'         => array_map(fn($r) => $this->mapRowToInstance($r), $rows),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
       ];
    }

    //Carrusel de productos destacados
    public function getProductosDestacados(int $limit = 20, string $empresa = null): array{
        $empresa = $empresa ?? config('app.company_code', '001');
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
            WHERE i.activo = 'S'
            AND i.empresa = ?
            AND i.imagen IS NOT NULL
            AND i.pvp1 > 0
            ORDER BY i.stock DESC
        ", [$empresa]);
        return array_map(fn($r) => $this->mapRowToInstance($r), $rows);
    }
}

