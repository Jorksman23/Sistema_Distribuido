<?php

namespace App\Http\Controllers;

use App\Models\ProductCatalog;
use App\Models\ProductPresentation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ProductsController extends Controller
{

    /**
     * Endpoint principal para obtener productos con sus variaciones.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Empresa y límite desde query string, con valores por defecto
            $empresa = $request->query('empresa', currentCompany());
            $limit   = (int) $request->query('limit', 50);

            // Llamar al catálogo (que une products_model y ProductPresentation)
            $productos = ProductCatalog::getCatalog($limit, $empresa);

            return response()->json([
                'success' => true,
                'empresa' => $empresa,
                'total'   => count($productos),
                'data'    => $productos,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            // Manejo de errores
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }




}
