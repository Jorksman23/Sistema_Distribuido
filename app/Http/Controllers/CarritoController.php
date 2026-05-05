<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarritoModel;
use Throwable;

class CarritoController extends Controller
{
    protected $carrito;

    public function __construct()
    {
        $this->carrito = new CarritoModel();
    }

    // ── Listar carrito de un usuario ─────────────────────
    public function index(Request $request, string $userId)
    {
        try {
            $items = $this->carrito->getCarritoByUser($userId);

            return view('carrito.carrito', [
                'userId' => $userId,
                'items'  => $items,
                'total'  => count($items),
            ]);
        } catch (Throwable $e) {
            return view('errores.500', [
                'mensaje' => 'Error al obtener carrito: ' . $e->getMessage()
            ]);
        }
    }

    // ── Agregar producto ─────────────────────────────────
    public function add(Request $request)
    {
        $request->validate([
            'codigo_item'  => 'required|string',
            'nombre'       => 'required|string',
            'costo_real'   => 'required|numeric',
            'pvp3'         => 'required|numeric',
            'cantidad'     => 'required|integer|min:1',
            'cod_cliente'  => 'required|string',
        ]);

        try {
            $this->carrito->addProducto($request->all());

            return response()->json(['success' => true, 'message' => 'Producto agregado al carrito']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ── Actualizar cantidad ──────────────────────────────
    public function update(Request $request)
    {
        $request->validate([
            'id_item_web' => 'required|integer',
            'cantidad'    => 'required|integer|min:1',
        ]);

        try {
            $this->carrito->updateCantidad($request->id_item_web, $request->cantidad);

            return response()->json(['success' => true, 'message' => 'Cantidad actualizada']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ── Eliminar producto ────────────────────────────────
    public function remove(Request $request)
    {
        $request->validate([
            'id_item_web' => 'required|integer',
        ]);

        try {
            $this->carrito->removeProducto($request->id_item_web);

            return response()->json(['success' => true, 'message' => 'Producto eliminado del carrito']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
