<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProductService;
use Throwable;


class ProductsController {

    /**
     * Mostrar un producto con sus presentaciones
     */
    public function show(Request $request, string $codigo)
    {

        try {
            $empresa  = $request->query('empresa', currentCompany());
            $service  = new ProductService();
            $producto = $service->getProductWithPresentations($codigo, $empresa);

            if (empty($producto)) {
                return view('errores.404', ['mensaje' => 'Producto no encontrado']);
            }
            // Ubicaciones del producto base (sin presentación)
            $ubicaciones = $service->getUbicacionesProducto($codigo, $empresa);

            // Ubicaciones por presentación (para JS)
            $ubicacionesPorPresentacion = [];
            foreach ($producto['presentaciones'] as $pres) {
                $ubicacionesPorPresentacion[$pres->codigo] =
                    $service->getUbicacionesPresentacion($pres->codigo, $empresa);
            }

            return view('products.show', [
                'empresa'  => $empresa,
                'producto' => $producto,
                'ubicaciones'                => $ubicaciones,
                'ubicacionesPorPresentacion' => $ubicacionesPorPresentacion,
            ]);
        } catch (Throwable $e) {
            return redirect()->route('catalogo.index')
                ->withErrors(['error' => 'Error al obtener producto: ' . $e->getMessage()]);
        }
    }

    /**
     * Listar catálogo de productos con filtros y paginación
     */
    public function index(Request $request)
    {
        try {
            $empresa = currentCompany();
            $page    = max(1, (int) $request->query('page', 1));

            // Filtros desde la request
            $filters = [
                'search'    => trim($request->query('q', '')),
                'grupo'     => trim($request->query('grupo', '')),
                'linea'     => trim($request->query('linea', '')),
                'ubicacion' => trim($request->query('ubicacion', '')),
                'precioMin' => (float) $request->query('precio_min', 0),
                'precioMax' => (float) $request->query('precio_max', 0),
                'orden'     => $request->query('orden', 'codigo'),
            ];

            $service = new ProductService();
            $result  = $service->getCatalogo($filters, $page, $empresa);

            return view('catalogo.catalogo', [
                'empresa'     => $empresa,
                'productos'   => $result['data'],
                'total'       => $result['total'],
                'currentPage' => $result['current_page'],
                'lastPage'    => $result['last_page'],
                'perPage'     => $result['per_page'],
                'filters'     => $filters,
            ] + $service->getFiltros($empresa));
        } catch (Throwable $e) {
            return view('errors.500', ['mensaje' => 'Error al cargar catálogo: ' . $e->getMessage()]);
        }
    }


}
