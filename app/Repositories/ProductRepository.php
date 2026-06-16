<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ProductRepository
{
    protected $connection = 'odbc';

    //Normalizador de cadenas para búsqueda
    private function normalizeString(string $text): string{
        $from = ['á','é','í','ó','ú','ü','ñ','Á','É','Í','Ó','Ú','Ü','Ñ'];
        $to   = ['a','e','i','o','u','u','n','a','e','i','o','u','u','n'];
        return str_replace($from, $to, mb_strtolower($text, 'UTF-8'));
    }

    //Listar productos activos
    public function getActiveProducts(int $limit, string $empresa): array{
        return DB::connection($this->connection)->select("
            SELECT TOP {$limit}
                i.codigo, i.empresa, i.descripcion1,
                i.pvp1, i.imagen, i.stock,
                l.linea AS categoria
            FROM DBA.in_item i
            LEFT JOIN DBA.in_linea l
                ON i.linea = l.codigo AND l.empresa = i.empresa
            WHERE i.stock in ('S', 'N') AND i.empresa = ?
            ORDER BY i.codigo
        ", [$empresa]);
    }


    public function getProductosDestacados(int $limit, string $empresa): array{
        return DB::connection($this->connection)->select("
            SELECT TOP {$limit}
                i.codigo, i.empresa, i.descripcion1,
                i.pvp1, i.imagen, i.stock,
                l.linea AS categoria
            FROM DBA.in_item i
            LEFT JOIN DBA.in_linea l
                ON i.linea = l.codigo AND l.empresa = i.empresa
            WHERE i.stock in ('S', 'N')
            AND i.empresa = ?
            AND i.imagen IS NOT NULL
            AND i.pvp1 > 0
            ORDER BY i.stock DESC
        ", [$empresa]);
    }

    //Busqueda de productos por descripción
    public function searchProducts(string $search, string $empresa): array{
        $searchNormalizado = '%' . $this->normalizeString($search) . '%';
        return DB::connection($this->connection)->select("
            SELECT
                i.codigo, i.empresa, i.descripcion1,
                i.pvp1, i.imagen, i.stock,
                l.linea AS categoria
            FROM DBA.in_item i
            LEFT JOIN DBA.in_linea l
                ON i.linea = l.codigo AND l.empresa = i.empresa
            WHERE i.stock in ('S', 'N')
            AND i.empresa = ?
            AND (i.descripcion1 LIKE ? OR i.descripcion1 LIKE ?)
            ORDER BY i.codigo
        ", [$empresa, '%' . $search . '%', $searchNormalizado]);
    }


    //Obtener Grupos - con caché de cada 6 hrs para optimizar
    public function getGrupos(string $empresa): array{
        $rows = DB::connection($this->connection)->select("
            SELECT codigo, grupo
            FROM DBA.in_grupo
            WHERE empresa = ?
            ORDER BY grupo
        ", [$empresa]);
        return array_map(fn($r) => [
            'codigo' => $r->codigo,
            'grupo'  => \App\Models\ProductsModel::cleanString($r->grupo),
        ], $rows);
    }

    //Obtener Líneas - Agg caché de cada 6 hrs para optimizar
    public function getLineas(string $empresa): array{
        $rows = DB::connection($this->connection)->select("
            SELECT codigo, linea
            FROM DBA.in_linea
            WHERE empresa = ?
            ORDER BY linea
        ", [$empresa]);
        return array_map(fn($r) => [
            'codigo' => $r->codigo,
            'linea'  => \App\Models\ProductsModel::cleanString($r->linea),
        ], $rows);
    }

    //Obtener Ubicaciones - Se Agg caché de cada 6 hrs para optimizar (Providers)
    //Filtra por view_on_tienda = 'S' para que el cliente solo vea ubicaciones habilitadas,
    //Esto evita que aparezcan las ubicaciones ocultas en el filtrado
    public function getUbicaciones(string $empresa): array{
        $rows = DB::connection($this->connection)->select("
            SELECT codigo, ubicacion
            FROM DBA.in_ubicacion
            WHERE empresa = ?
            AND view_on_tienda = 'S'
            ORDER BY ubicacion
        ", [$empresa]);
        return array_map(fn($r) => (array) $r, $rows);
    }

    //Obtener ubicacion in_item disponibles de un producto sin presentacio
    //Filtra por view_on_tienda = 'S' para mostrar solo ubicaciones visibles en la tienda,
    //excluyendo ubicaciones ocultas como (Bodega,Sauces).
    public function getUbicacionesProducto(string $codigo, string $empresa): array{
        return DB::connection($this->connection)->select("
            SELECT
                e.ubicacion,
                u.ubicacion AS nombre_ubicacion,
                e.existencia AS stock
            FROM DBA.in_existencia e
            LEFT JOIN DBA.in_ubicacion u
                ON u.codigo = e.ubicacion
                AND u.empresa = e.empresa
            WHERE e.producto = ?
            AND e.empresa = ?
            AND e.existencia > 0
            AND u.view_on_tienda = 'S'
            ORDER BY e.existencia DESC
        ", [$codigo, $empresa]);
    }

    //Obtiene las ubicaciones disponibles de una presentación específica (in_item_presentacion).
    //Filtra por view_on_tienda = 'S' para que el selector dinámico en el detalle del producto
    //solo muestre ubicaciones visibles al cliente al elegir un modelo.
    public function getUbicacionesPresentacion(int $codigoPresentacion, string $empresa): array{
        return DB::connection($this->connection)->select("
            SELECT
                ep.ubicacion,
                u.ubicacion AS nombre_ubicacion,
                ep.cantidad AS stock
            FROM DBA.in_existencia_presentacion ep
            LEFT JOIN DBA.in_ubicacion u
                ON u.codigo = ep.ubicacion
                AND u.empresa = ep.empresa
            WHERE ep.item_presentacion = ?
            AND ep.empresa = ?
            AND ep.cantidad > 0
            AND u.view_on_tienda = 'S'
            ORDER BY ep.cantidad DESC
        ", [$codigoPresentacion, $empresa]);
    }

    /**
     * Obtiene productos paginados con filtros dinámicos.
     * El JOIN con in_existencia usa subconsulta para excluir ubicaciones con view_on_tienda = 'N',
     * garantizando que stock_total solo refleje existencias visibles en la tienda.
     */
    public function getPaginatedProducts(
        int     $page,
        int     $perPage,
        string  $empresa,
        string  $search    = '',
        string  $grupo     = '',
        string  $linea     = '',
        string  $ubicacion = '',
        float   $precioMin = 0,
        float   $precioMax = 0,
        string  $orden     = 'codigo'
    ): array {
        $startAt = (($page - 1) * $perPage) + 1;
        $where   = "WHERE i.stock in ('S', 'N') AND i.empresa = ?";
        $params  = [$empresa];
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
        if ($ubicacion !== '') {
            $where .= "
                AND (
                    EXISTS (
                        SELECT 1 FROM DBA.in_existencia e
                        WHERE e.empresa = i.empresa
                        AND e.producto = i.codigo
                        AND e.ubicacion = ?
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM DBA.in_item_presentacion p
                        INNER JOIN DBA.in_existencia_presentacion ep
                            ON ep.item_presentacion = p.codigo
                            AND ep.empresa = p.empresa
                        WHERE p.empresa = i.empresa
                        AND p.producto = i.codigo
                        AND ep.ubicacion = ?
                    )
                )";
            $params[] = $ubicacion;
            $params[] = $ubicacion;
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
            FROM DBA.in_item i {$where}
        ", $params);
        // Subconsulta IN filtra ubicaciones con view_on_tienda = 'S',
        // evitando que stock de ubicaciones ocultas se sume al total visible.
        $rows = DB::connection($this->connection)->select("
            SELECT TOP {$perPage} START AT {$startAt}
                i.codigo, i.empresa, i.descripcion1,
                i.pvp1, i.imagen, i.stock, i.grupo,
                l.linea AS categoria,
                COALESCE(SUM(e.existencia), 0) AS stock_total,
                CASE WHEN EXISTS (
                    SELECT 1 FROM DBA.in_item_presentacion p
                    WHERE p.producto = i.codigo
                    AND p.empresa = i.empresa
                    AND p.mostrar = 'S'
                ) THEN 1 ELSE 0 END AS tiene_presentaciones
            FROM DBA.in_item i
            LEFT JOIN DBA.in_linea l
                ON i.linea = l.codigo AND l.empresa = i.empresa
            LEFT JOIN DBA.in_existencia e
                ON e.producto = i.codigo AND e.empresa = i.empresa
                AND e.ubicacion IN (
                    SELECT codigo FROM DBA.in_ubicacion
                    WHERE empresa = i.empresa
                    AND view_on_tienda = 'S'
                )
            {$where}
            GROUP BY
                i.codigo, i.empresa, i.descripcion1,
                i.pvp1, i.imagen, i.stock, i.grupo, l.linea
            ORDER BY {$orderBy}
        ", $params);
        return [
            'rows'     => $rows,
            'total'    => $totalRow->total ?? 0,
            'per_page' => $perPage,
            'page'     => $page,
            'last_page'=> (int) ceil(($totalRow->total ?? 0) / $perPage),
        ];
    }


    public function findByCodigo(string $codigo, string $empresa): ?object{
        return DB::connection($this->connection)->selectOne("
            SELECT TOP 1 *
            FROM DBA.in_item
            WHERE codigo = ? AND empresa = ? AND stock in ('S', 'N')
        ", [$codigo, $empresa]);
    }

    /**
     * Obtiene productos relacionados según el mismo grupo (subcategoría) del producto actual.
     * Excluye el producto actual y filtra stock por ubicaciones visibles (view_on_tienda = 'S').
     */
    public function getRelacionados(string $codigo, string $grupo, string $empresa, int $limit = 8): array
    {
        return DB::connection($this->connection)->select("
            SELECT TOP {$limit}
                i.codigo, i.empresa, i.descripcion1,
                i.pvp1, i.imagen, i.stock, i.grupo,
                l.linea AS categoria,
                COALESCE(SUM(e.existencia), 0) AS stock_total,
                CASE WHEN EXISTS (
                    SELECT 1 FROM DBA.in_item_presentacion p
                    WHERE p.producto = i.codigo
                    AND p.empresa = i.empresa
                    AND p.mostrar = 'S'
                ) THEN 1 ELSE 0 END AS tiene_presentaciones
            FROM DBA.in_item i
            LEFT JOIN DBA.in_linea l
                ON i.linea = l.codigo AND l.empresa = i.empresa
            LEFT JOIN DBA.in_existencia e
                ON e.producto = i.codigo AND e.empresa = i.empresa
                AND e.ubicacion IN (
                    SELECT codigo FROM DBA.in_ubicacion
                    WHERE empresa = i.empresa
                    AND view_on_tienda = 'S'
                )
            WHERE i.stock in ('S', 'N')
            AND i.empresa = ?
            AND i.grupo = ?
            AND i.codigo != ?
            GROUP BY
                i.codigo, i.empresa, i.descripcion1,
                i.pvp1, i.imagen, i.stock, i.grupo, l.linea
            ORDER BY RAND()
        ", [$empresa, $grupo, $codigo]);
    }
}
