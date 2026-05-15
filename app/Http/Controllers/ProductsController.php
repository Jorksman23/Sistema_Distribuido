<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductsModel;
use App\Models\ProductPresentation;
use Throwable;


class ProductsController extends Controller
{
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
            return redirect()->route('catalogo.index')
                ->withErrors(['error' => 'Error al obtener producto: ' . $e->getMessage()]);
        }
    }

    public function index(Request $request){
        $empresa = currentCompany();
        $page    = max(1, (int) $request->query('page', 1));
        $model   = new ProductsModel();

        $filters = [
            'search'    => trim($request->query('q', '')),
            'grupo'     => trim($request->query('grupo', '')),
            'linea'     => trim($request->query('linea', '')),
            'ubicacion' => trim($request->query('ubicacion', '')),
            'precioMin' => (float) $request->query('precio_min', 0),
            'precioMax' => (float) $request->query('precio_max', 0),
            'orden'     => $request->query('orden', 'codigo'),
        ];

        $result = $model->getPaginatedProducts(
            $page, 20, $empresa,
            $filters['search'],
            $filters['grupo'],
            $filters['linea'],
            $filters['ubicacion'],
            (float) $filters['precioMin'],
            (float) $filters['precioMax'],
            $filters['orden'],
        );

        return view('catalogo.catalogo', [
            'empresa'     => $empresa,
            'productos'   => $result['data'],
            'total'       => $result['total'],
            'currentPage' => $result['current_page'],
            'lastPage'    => $result['last_page'],
            'perPage'     => $result['per_page'],
            'filters'     => $filters,
            'grupos'      => $model->getGrupos($empresa),
            'lineas'      => $model->getLineas($empresa),
            'ubicaciones' => $model->getUbicaciones($empresa),
        ]);
    }
}
