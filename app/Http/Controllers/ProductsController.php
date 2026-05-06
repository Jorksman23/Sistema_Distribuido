<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductsModel;
use App\Models\ProductPresentation;
use Throwable;

class ProductsController extends Controller
{
    // Mostrar listado de productos (solo básicos, sin stock ni presentaciones)
    public function index(Request $request)
    {
        $empresa   = $request->query('empresa', currentCompany());

        // Traemos hasta 50 productos activos con imagen principal
        $productos = (new ProductsModel())->getActiveProducts(50, $empresa);

        return view('products.index', [
            'empresa'   => $empresa,
            'productos' => $productos,
            'total'     => count($productos),
        ]);
    }

    // Mostrar detalle de un producto específico con presentaciones
    public function show(Request $request, string $codigo)
    {
        try {
            $empresa  = $request->query('empresa', currentCompany());

            // Usamos directamente ProductPresentation para obtener producto + presentaciones
            $producto = (new ProductPresentation())->getByProduct($codigo, $empresa, 5);

            if (empty($producto)) {
                return view('errores.404', ['mensaje' => 'Producto no encontrado']);
            }

            return view('products.show', [
                'empresa'  => $empresa,
                'producto' => $producto,
            ]);
        } catch (Throwable $e) {
            return redirect()->route('products.index')
                ->withErrors(['error' => 'Error al obtener producto: ' . $e->getMessage()]);
        }
    }
}
