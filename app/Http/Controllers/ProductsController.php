<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductsModel;
use App\Models\ProductPresentation;
use Throwable;

class ProductsController extends Controller
{
    // Mostrar listado de productos (solo básicos, sin stock ni presentaciones)
    // public function index(Request $request)
    // {
    //     $empresa   = $request->query('empresa', currentCompany());
    //     // Traemos hasta 50 productos activos con imagen principal
    //     $productos = (new ProductsModel())->getActiveProducts(50, $empresa);

    //     return view('products.index', [
    //         'empresa'   => $empresa,
    //         'productos' => $productos,
    //         'total'     => count($productos),
    //     ]);
    // }

    //Producto Paginado Catalogo/vista-web.app
    public function index(Request $request){
        $empresa = $request->query('empresa', currentCompany());
        $page    = max(1, (int) $request->query('page', 1));
        $search  = trim($request->query('q',''));

        $result = (new ProductsModel())->getPaginatedProducts($page, 12, $empresa);

        return view('catalogo.catalogo', [
            'empresa'      => $empresa,
            'productos'    => $result['data'],
            'total'        => $result['total'],
            'currentPage'  => $result['current_page'],
            'lastPage'     => $result['last_page'],
            'perPage'      => $result['per_page'],
            'search'       => $search,
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

        public function indexSearch(Request $request){
        $empresa = currentCompany();
        $search  = trim($request->query('q', ''));
        $page    = max(1, (int) $request->query('page', 1));

        if ($search !== '') {
            // Búsqueda específica
            $productos = (new ProductsModel())->searchProducts($search, $empresa);

            return view('Catalogo.catalogo', [
                'empresa'     => $empresa,
                'productos'   => $productos,
                'total'       => count($productos),
                'currentPage' => 1,
                'lastPage'    => 1,
                'perPage'     => count($productos),
                'search'      => $search,
            ]);
        }
        // Sin búsqueda — paginación normal
        $result = (new ProductsModel())->getPaginatedProducts($page, 12, $empresa);

        return view('Catalogo.catalogo', [
            'empresa'     => $empresa,
            'productos'   => $result['data'],
            'total'       => $result['total'],
            'currentPage' => $result['current_page'],
            'lastPage'    => $result['last_page'],
            'perPage'     => $result['per_page'],
            'search'      => '',
        ]);
    }
}
