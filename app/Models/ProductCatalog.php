<?php

namespace App\Models;

class ProductCatalog
{
    public static function getCatalog(int $limit = 50, string $empresa = null): array
{
    $empresa = $empresa ?? currentCompany();

    // 1. Productos activos
    $productos = products_model::getActiveProducts($limit, $empresa);

    // 2. Presentaciones
    $presentaciones = ProductPresentation::getPresentations($empresa);

    // 3. Agrupar presentaciones por producto
    $presentacionesPorProducto = [];
    foreach ($presentaciones as $p) {
        $presentacionesPorProducto[$p['producto']][] = [
            'url'    => products_model::getProductImageUrl(products_model::cleanString($p['foto']), $empresa),
            'nombre' => products_model::cleanString($p['nombre']),
        ];
    }

    // 4. Unir productos con sus presentaciones
    return array_map(function ($prod) use ($presentacionesPorProducto) {
        $codigo = $prod['codigo'];
        $prod['imagenes'] = $presentacionesPorProducto[$codigo] ?? [];
        return $prod;
    }, $productos);
}
}
