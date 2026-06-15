<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Parametro;

class AppServiceFooter extends ServiceProvider
{
    
    public function boot(): void
    {
        $empresa = currentCompany();
        $parametros = Parametro::where('empresa', $empresa)
            ->pluck('descripcion', 'parametro');
        View::share('footerData', [
            'descripcion' =>$parametros['PB1'] ?? '',
            'facebook'  => $parametros['FB'] ?? '#',
            'instagram' => $parametros['IG'] ?? '#',
            'twitter'   => $parametros['TW'] ?? '#',
            'youtube'   =>$parametros['YT'] ?? '',
            //'logo'    => !empty($parametros['LG'])? asset('static/image/product/' . $parametros['LG']): '',
            'correo'    => $parametros['EM1'] ?? '',
            'direccion' => $parametros['UB1'] ?? '',
            'telefonos'   => [$parametros['WA'] ?? '',

             ],

        ]);
    }
}
