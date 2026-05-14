<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarritoModel;
use App\Models\ProductsModel;
use App\Models\ProductPresentation;
use Throwable;

class CarritoController extends Controller
{
    protected $carrito;

    public function __construct()
    {
        $this->carrito = new CarritoModel();
    }

    // ── Mostrar carrito ──────────────────────────────────
    public function index()
    {
        $codCliente = (string) session('user_id');

        try {
            $items = $this->carrito->getCarritoByUser($codCliente);
            $total = $this->carrito->getTotal($codCliente);
            $count = count($items);

            return view('cart.index', [
                'items' => $items,
                'total' => number_format($total, 2, '.', ''),
                'count' => $count,
            ]);
        } catch (Throwable $e) {
            return view('errors.500', [
                'mensaje' => 'Error al obtener carrito: ' . $e->getMessage(),
            ]);
        }
    }

    // ── Agregar producto desde catálogo (sin variante) ───
    public function add(Request $request)
    {
        $request->validate([
            'codigo_item'  => 'required|string',
            'nombre'       => 'nullable|string',
            'pvp3'         => 'nullable|numeric',
            'imagen'       => 'nullable|string',
            'presentacion' => 'nullable|integer',
        ]);

        $codCliente   = (string) session('user_id');
        $presentacion = (int) ($request->presentacion ?? 0);

        try {
            // Si ya existe esa combinacion producto+presentacion
            // solo incrementa cantidad
            if ($this->carrito->exists($codCliente, $request->codigo_item, $presentacion)) {
                $item = $this->carrito->getItemByProducto(
                    $codCliente,
                    $request->codigo_item,
                    $presentacion
                );

                if ($item) {
                    $this->carrito->updateCantidad(
                        $item->id_item_web,
                        $codCliente,
                        $item->cantidad + 1
                    );
                }
            } else {
                // Si no vienen datos del producto en el request
                // los buscamos en BD (solo cuando viene del detalle
                // sin pasar los campos hidden)
                $nombre = $request->nombre;
                $pvp3   = $request->pvp3;
                $imagen = $request->imagen;

                if (!$nombre || !$pvp3) {
                    $producto = (new ProductsModel())->findByCodigo(
                        $request->codigo_item,
                        currentCompany()
                    );

                    if (!$producto) {
                        return back()->withErrors(['error' => 'Producto no encontrado']);
                    }

                    $nombre = $nombre ?? $producto->descripcion1;
                    $pvp3   = $pvp3   ?? $producto->pvp1;
                    $imagen = $imagen ?? $producto->imagen;
                }

                $this->carrito->add([
                    'codigo_item'  => $request->codigo_item,
                    'nombre'       => $nombre,
                    'costo_real'   => $pvp3,
                    'pvp3'         => $pvp3,
                    'cantidad'     => 1,
                    'cod_cliente'  => $codCliente,
                    'imagen'       => $imagen,
                    'iva'          => 'N',
                    'presentacion' => $presentacion,
                ]);
            }

            // Limpiar caché del contador
            session()->forget('carrito_count');

        } catch (Throwable $e) {
            return back()->withErrors(['error' => 'Error al agregar: ' . $e->getMessage()]);
        }

        return back()->with('success_cart', '¡Producto agregado al carrito!');
    }

    // ── Actualizar cantidad ──────────────────────────────
    public function update(Request $request)
    {
        $request->validate([
            'id_item_web' => 'required|integer',
            'cantidad'    => 'required|integer|min:1|max:99',
        ]);

        $codCliente = (string) session('user_id');

        try {
            $this->carrito->updateCantidad(
                $request->id_item_web,
                $codCliente,
                $request->cantidad
            );

            session()->forget('carrito_count');

        } catch (Throwable $e) {
            dd($e->getMessage());
            //return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }

        return back();
    }

    // ── Eliminar producto ────────────────────────────────
    public function remove(Request $request)
    {
        $request->validate([
            'id_item_web' => 'required|integer',
        ]);

        $codCliente = (string) session('user_id');

        try {
            $this->carrito->remove($request->id_item_web, $codCliente);
            session()->forget('carrito_count');

        } catch (Throwable $e) {
            return back()->withErrors(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }

        return back();
    }

    // ── Vaciar carrito ───────────────────────────────────
    public function vaciar()
    {
        $codCliente = (string) session('user_id');

        try {
            $this->carrito->vaciar($codCliente);
            session()->forget('carrito_count');

        } catch (Throwable $e) {
            return back()->withErrors(['error' => 'Error al vaciar: ' . $e->getMessage()]);
        }

        return redirect()->route('carrito.index');
    }
}
