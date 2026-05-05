<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\ProductsModel;
use App\Models\ProductPresentation;



class ProductCatalog
{
    protected $connection = 'odbc';

    // ── Obtener catálogo completo ────────────────────────
    public function getCatalog(int $limit = 50, string $empresa = null): array
    {
        $empresa = $empresa ?? currentCompany();

        // 1. Productos activos
        $productos = (new ProductsModel())->getActiveProducts($limit, $empresa);

        // 2. Presentaciones activas
        $presentaciones = (new ProductPresentation())->getPresentations($empresa);

        // 3. Agrupar presentaciones por producto
        $presentacionesPorProducto = [];
        foreach ($presentaciones as $p) {
            $presentacionesPorProducto[$p->producto][] = [
                'url'    => productImageUrl(ProductPresentation::cleanString($p->foto)),
                'nombre' => ProductPresentation::cleanString($p->nombre),
            ];
        }

        // 4. Unir productos con sus presentaciones
        return array_map(function ($prod) use ($presentacionesPorProducto) {
            $codigo = $prod->codigo;
            $prod->imagenes = $presentacionesPorProducto[$codigo] ?? [];
            return $prod;
        }, $productos);
    }


    public function getProductWithPresentations(string $codigoProducto, string $empresa = null): array
    {
        $empresa = $empresa ?? currentCompany();

        $rows = DB::connection($this->connection)->select("
            SELECT ip.foto, ip.nombre, ip.producto, i.descripcion1, i.pvp1, i.stock
            FROM in_item_presentacion ip
            INNER JOIN in_item i
                ON ip.producto = i.codigo AND ip.empresa = i.empresa
            WHERE ip.producto = ? AND ip.empresa = ? AND ip.mostrar = 'S'
        ", [$codigoProducto, $empresa]);

        return array_map(function ($row) {
            return [
                'producto'    => $row->producto,
                'nombre'      => ProductPresentation::cleanString($row->nombre),
                'foto'        => $row->foto,
                'foto_url'    => productImageUrl($row->foto),
                'descripcion' => ProductsModel::cleanString($row->descripcion1),
                'precio'      => number_format((float)$row->pvp1, 2, '.', ''),
                'stock'       => $row->stock,
            ];
        }, $rows);
    }
}
