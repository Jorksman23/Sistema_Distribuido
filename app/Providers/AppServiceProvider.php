<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Empresa;
use App\Models\WishListModel;
use App\Models\CarritoModel;
use App\Models\Parametro;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    public function boot(): void{
        // Cargar nuestro Helper de Empresa e Imágenes
        require_once app_path('Helpers/CompanyHelper.php');
        // Compartir nombre de empresa en todas las vistas (lo que ya tenías)
        View::share('empresaNombre', Empresa::getNombre());
        // Compartir conteo de wishlist en todas las vistas
        View::composer('*', function ($view) {
    if (session('user_id')) {
            $wishlist = new WishListModel();
            $empresa  = config('app.company_code', '001');
            $codCliente = (string) session('user_id');
        // Cachear en sesión para no hacer query en cada vista
    if (!session()->has('wish_codes')) {
            $rows = $wishlist->getByCliente($codCliente, $empresa);
            $codes = array_map(fn($item) => $item->codigo_item, $rows);
            session(['wish_codes' => $codes]);
        }
        $codes = session('wish_codes', []);
        $view->with('wishCount', count($codes));
        $view->with('wishCodes', $codes);
        // Carrito
    if (!session()->has('carrito_count')) {
            $cartRepository = new \App\Repositories\CartRepository();
            session(['carrito_count' => $cartRepository->count($codCliente)]);
        }
        $view->with('carritoCount', session('carrito_count', 0));
       } else {
        $view->with('wishCount', 0);
        $view->with('wishCodes', []);
        $view->with('carritoCount', 0);
        }
      });
    }
}
