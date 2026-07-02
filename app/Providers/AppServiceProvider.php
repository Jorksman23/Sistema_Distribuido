<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Empresa;
use App\Models\Parametro;
use App\Repositories\WishListRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    public function boot(): void{
        // Forzar HTTPS cuando la app corre detrás del túnel de ngrok (para pruebas de pasarela)
        if (str_contains((string) config('app.url'), 'ngrok')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        // Cargar nuestro Helper de Empresa e Imágenes
        require_once app_path('Helpers/CompanyHelper.php');
        // Compartir nombre de empresa en todas las vistas (lo que ya tenías)
        View::share('empresaNombre', Empresa::getNombre());
        // Compartir conteo de wishlist en todas las vistas
        View::composer('*', function ($view) {
    if (session('user_id')) {
            $wishlist = new WishListRepository();
            $empresa  = config('app.company_code', '?');
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
    $empresa = currentCompany();
    $parametros = Parametro::where('empresa', $empresa)
        ->pluck('descripcion', 'parametro');

    View::share('homeData', [
        'publicidad'   => $parametros['PB1'] ?? '',
        'slider'       => [
            $parametros['BP3'] ?? '',
            $parametros['BP4'] ?? '',
            $parametros['BP5'] ?? '',
            $parametros['BP6'] ?? '',
            $parametros['BP7'] ?? '',
        ],
        'tituloSeccion'=> $parametros['TL18'] ?? '',
        'masVendidos'  => $parametros['TL2'] ?? '',
        'ofertas'      => $parametros['TL3'] ?? '',
        'destacados'   => $parametros['TL7'] ?? '',
        'anuncios'     => [
            $parametros['AN1'] ?? '',
            $parametros['AN2'] ?? '',
            $parametros['AN3'] ?? '',
            $parametros['AN4'] ?? '',
        ],
        'informativo'  => $parametros['PB3'] ?? '',
        'subtitulos'   => [
            $parametros['TL4'] ?? '',
            $parametros['TL5'] ?? '',
            $parametros['TL6'] ?? '',
        ],
    ]);
}
}
