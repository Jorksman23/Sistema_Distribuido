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

    // Mostrar listado de productos
    public function index(Request $request)
{
    $empresa = $request->query('empresa', currentCompany());
    $productos = $this->catalog->getCatalog($empresa);

    return view('products.index', [
        'empresa'   => $empresa,
        'productos' => $productos,
        'total'     => count($productos),
    ]);
}


    // Mostrar detalle de un producto específico
        public function show(Request $request, string $codigo)
        {
            try {
                $empresa  = $request->query('empresa', currentCompany());
                $producto = $this->catalog->getProductWithPresentations($codigo, $empresa);

                if (!$producto) {
                    // Mostrar vista de error personalizada
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

