<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarritoModel;

class CarritoController extends Controller
{
    // Propiedad tipada
    protected CarritoModel $carrito;

    public function __construct()
    {
        $this->carrito = new CarritoModel();
    }

    // Parámetro tipado como int
    public function index(int $userId)
    {
        $items = $this->carrito->getCarritoByUser($userId);
        return response()->json($items);
    }

    public function add(Request $request)
    {
        $this->carrito->addProducto(
            (int)$request->user_id,
            (int)$request->producto_id,
            (int)$request->cantidad
        );
        return response()->json(['message' => 'Producto agregado al carrito']);
    }

    public function update(Request $request)
    {
        $this->carrito->updateProducto(
            (int)$request->user_id,
            (int)$request->producto_id,
            (int)$request->cantidad
        );
        return response()->json(['message' => 'Producto actualizado']);
    }

    public function remove(Request $request)
    {
        $this->carrito->removeProducto(
            (int)$request->user_id,
            (int)$request->producto_id
        );
        return response()->json(['message' => 'Producto eliminado del carrito']);
    }
}