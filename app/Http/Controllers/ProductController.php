<?php

namespace App\Http\Controllers;

use App\Models\products_model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $empresa = $request->query('empresa', '001');
        $limit   = (int) $request->query('limit', 20);

        $productos = products_model::getActiveProducts($limit, $empresa);

        return response()->json([
            'success'      => true,
            'empresa'      => $empresa,
            'total'        => count($productos),
            'image_server' => products_model:: getImageServerUrl($empresa),
            'data'         => $productos,
        ]);
    }
}
