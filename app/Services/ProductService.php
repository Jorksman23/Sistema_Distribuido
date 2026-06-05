<?php

namespace App\Services;

use App\Models\ProductsModel;
use App\Models\ProductPresentation;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected ProductsModel $productsModel;
    protected ProductPresentation $presentationModel;

    public function __construct()
    {
        $this->productsModel     = new ProductsModel();
        $this->presentationModel = new ProductPresentation();
    }

    /**
     * Obtener catálogo con filtros y paginación
     */
    public function getCatalogo(array $filters, int $page, string $empresa): array
    {
        return $this->productsModel->getPaginatedProducts(
            $page,
            40, // productos por página
            $empresa,
            $filters['search']    ?? '',
            $filters['grupo']     ?? '',
            $filters['linea']     ?? '',
            $filters['ubicacion'] ?? '',
            (float) ($filters['precioMin'] ?? 0),
            (float) ($filters['precioMax'] ?? 0),
            $filters['orden']     ?? 'codigo',
        );
    }

    /**
     * Obtener producto con sus presentaciones
     */
    public function getProductWithPresentations(string $codigo, string $empresa): array
    {
        return $this->presentationModel->getByProduct($codigo, $empresa);
    }

    /**
     * Obtener productos destacados (carrusel)
     */
    public function getDestacados(string $empresa, int $limit = 20): array
    {
        return $this->productsModel->getProductosDestacados($limit, $empresa);
    }

    /**
     * Obtener filtros auxiliares (grupos, líneas, ubicaciones)
     */
    public function getFiltros(string $empresa): array
    {
        return [
            'grupos'      => $this->productsModel->getGrupos($empresa),
            'lineas'      => $this->productsModel->getLineas($empresa),
            'ubicaciones' => $this->productsModel->getUbicaciones($empresa),
        ];
    }

    public function getUbicacionesProducto(string $codigo, string $empresa): array
{
    return DB::connection('odbc')->select("
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
        ORDER BY e.existencia DESC
    ", [$codigo, $empresa]);
}

public function getUbicacionesPresentacion(int $codigoPresentacion, string $empresa): array
{
    return DB::connection('odbc')->select("
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
        ORDER BY ep.cantidad DESC
    ", [$codigoPresentacion, $empresa]);
}
}
