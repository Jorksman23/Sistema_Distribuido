<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCatalog;
use Throwable;

class ProductsController extends Controller
{
    protected $catalog;

    public function __construct()
    {
        // Instanciamos el catálogo de productos
        $this->catalog = new ProductCatalog();
    }

    // Mostrar listado de productos (solo básicos, sin stock)
    public function index(Request $request)
    {
        $empresa   = $request->query('empresa', currentCompany());
        // Traemos hasta 50 productos, sin presentaciones ni stock
        $productos = $this->catalog->getCatalog(52, $empresa);

        return view('products.index', [
            'empresa'   => $empresa,
            'productos' => $productos,
            'total'     => count($productos),
        ]);
    }

    // Mostrar detalle de un producto específico (con stock y presentaciones)
    public function show(Request $request, string $codigo)
    {
        try {
            $empresa  = $request->query('empresa', currentCompany());
            $producto = $this->catalog->getProductWithPresentations($codigo, $empresa);

            if (!$producto) {
                return view('errores.404', ['mensaje' => 'Producto no encontrado']);
            }

            return view('products.show', [
                'empresa'  => $empresa,
                'codigo'   => $codigo,
                'producto' => $producto,
            ]);
        } catch (Throwable $e) {
            return redirect()->route('products.index')
                ->withErrors(['error' => 'Error al obtener producto: ' . $e->getMessage()]);
        }
    }
}
