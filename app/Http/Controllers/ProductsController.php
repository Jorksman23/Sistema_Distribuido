<?php

namespace App\Http\Controllers;

use App\Models\products_model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Retorna todos los productos activos de la empresa actual
     */
    public function index(Request $request): JsonResponse
    {
        $productos = products_model::getActiveProducts();

        return response()->json([
            'success' => true,
            'empresa' => currentCompany(),
            'total'   => count($productos),
            'data'    => $productos,
        ]);
    }
}
