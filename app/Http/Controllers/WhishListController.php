<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\WishListRepository;
use App\Models\ProductsModel;
use Throwable;



class WhishListController {

    protected $wishlist;

    public function __construct(WishListRepository $WishListRepository)
    {
        $this->wishlist = new $WishListRepository;
    }

    //Mostrar lista de deseos
    public function index()
    {
        $codCliente = (string) session('user_id');
        $empresa    = currentCompany();

        try {
            $items = $this->wishlist->getByCliente($codCliente, $empresa);

            return view('wishlist.wishlist', [
                'items'   => $items,
                'empresa' => $empresa,
            ]);
        } catch (Throwable $e) {
            return view('errors.500', [
                'mensaje' => 'Error al obtener lista de deseos: ' . $e->getMessage(),
            ]);
        }
    }

    //Toggle: agregar o eliminar
    public function toggle(Request $request)
    {
        $request->validate([
            'codigo_item' => 'required|string',
            'redirect_to' => 'nullable|string',
        ]);

        $codCliente = (string) session('user_id');
        $empresa    = currentCompany();

        try {
            $exists = $this->wishlist->exists($codCliente, $request->codigo_item, $empresa);

            if ($exists) {
                $this->wishlist->remove($codCliente, $request->codigo_item, $empresa);
            } else {
                $producto = (new ProductsModel())->findByCodigo($request->codigo_item, $empresa);

                if ($producto) {
                    $this->wishlist->add([
                        'cod_cliente' => $codCliente,
                        'codigo_item' => $producto->codigo,
                        'nombre'      => $producto->descripcion1,
                        'pvp3'        => $producto->pvp1,
                        'imagen'      => $producto->imagen,
                        'empresa'     => $empresa,
                    ]);
                }
            }
            session()->forget('wish_codes');
        } catch (Throwable $e) {
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
        if($request->redirect_to === 'wishlist.index'){
            return redirect()->route('wishlist.index');
        }
        // Redirige de vuelta a donde estaba el usuario
        return back();
    }

    //Obtener códigos en wishlist para la sesión
    public function getCodes(): array
    {
        if (!session('user_id')) return [];

        $codCliente = (string) session('user_id');
        $empresa    = currentCompany();

        try {
            $rows = $this->wishlist->getByCliente($codCliente, $empresa);
            return array_map(fn($item) => $item->codigo_item, $rows);
        } catch (Throwable $e) {
            return [];
        }
    }
}
