<?php

namespace App\Services;

use App\Models\ProductsModel;
use App\Models\ProductPresentation;

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
}
