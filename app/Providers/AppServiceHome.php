<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Parametro;

class AppServiceHome extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $empresa = currentCompany();
        $parametros = Parametro::where('empresa', $empresa)
            ->pluck('descripcion', 'parametro');

        View::share('homeData', [
            // Publicidad principal
            'publicidad'   => $parametros['PB1'] ?? '',

            // Slider principal
            'slider'       => [
                $parametros['BP3'] ?? '',
                $parametros['BP4'] ?? '',
                $parametros['BP5'] ?? '',
                $parametros['BP6'] ?? '',
                $parametros['BP7'] ?? '',
            ],

            // Secciones dinámicas
            'tituloSeccion'=> $parametros['TL18'] ?? '',
            'masVendidos'  => $parametros['TL2'] ?? '',
            'ofertas'      => $parametros['TL3'] ?? '',
            'destacados'   => $parametros['TL7'] ?? '',

            // Anuncios
            'anuncios'     => [
                $parametros['AN1'] ?? '',
                $parametros['AN2'] ?? '',
                $parametros['AN3'] ?? '',
                $parametros['AN4'] ?? '',
            ],

            // Texto informativo
            'informativo'  => $parametros['PB3'] ?? '',

            // Subtítulos adicionales
            'subtitulos'   => [
                $parametros['TL4'] ?? '',
                $parametros['TL5'] ?? '',
                $parametros['TL6'] ?? '',
            ],
        ]);
    }
}
