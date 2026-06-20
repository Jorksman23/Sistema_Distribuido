<?php

namespace App\Services;

use App\Models\ProductsModel;
use App\Models\ProductPresentation;
use App\Repositories\ProductRepository;


class ProductService{
    protected ProductsModel $productsModel;
    protected ProductPresentation $presentationModel;
    protected ProductRepository $productRepository;

    public function __construct()
    {
        $this->productsModel     = new ProductsModel();
        $this->presentationModel = new ProductPresentation();
        $this->productRepository = new ProductRepository();
    }

    public function getCatalogo(array $filters, int $page, string $empresa): array
    {
        $result = $this->productRepository->getPaginatedProducts(
            $page,
            40,//productos por página
            $empresa,
            $filters['search']    ?? '',
            $filters['grupo']     ?? '',
            $filters['linea']     ?? '',
            $filters['ubicacion'] ?? '',
            (float) ($filters['precioMin'] ?? 0),
            (float) ($filters['precioMax'] ?? 0),
            $filters['orden']     ?? 'codigo',
        );

        return [
            'data'         => array_map(fn($r) => $this->productsModel->mapRowToInstance($r), $result['rows']),
            'total'        => $result['total'],
            'per_page'     => $result['per_page'],
            'current_page' => $result['page'],
            'last_page'    => $result['last_page'],
        ];
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
        $rows = $this->productRepository->getProductosDestacados($limit, $empresa);
        return array_map(fn($r) => $this->productsModel->mapRowToInstance($r), $rows);
    }

    /**
     * Obtener filtros auxiliares (grupos, líneas)
     */
    public function getFiltros(string $empresa): array
    {
        return [
            'grupos'      => $this->productRepository->getGrupos($empresa),
            'lineas'      => $this->productRepository->getLineas($empresa),
        ];
    }
}
