<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Empresa;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Cargar nuestro Helper de Empresa e Imágenes
        require_once app_path('Helpers/CompanyHelper.php');

        // Compartir nombre de empresa en todas las vistas (lo que ya tenías)
        View::share('empresaNombre', Empresa::getNombre());
    }
}
