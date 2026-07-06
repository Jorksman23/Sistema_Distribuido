<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class AppServiceFooter extends ServiceProvider
{
    public function boot(): void
    {
        $empresa = DB::connection('odbc')->selectOne(
            "SELECT TOP 1 email, direccion_tienda, celular, telefono2, celular_rl
            FROM DBA.ge_empresa
            WHERE codigo = ?",
            [currentCompany()]
        );

        $parametros = DB::connection('odbc')->select(
            "SELECT parametro, descripcion FROM DBA.pw_parametros WHERE empresa = ?",
            [currentCompany()]
        );

        $parametrosMap = collect($parametros)->pluck('descripcion', 'parametro');

        View::share('footerData', [
            'correo'     => $empresa->email ?? '',
            'direccion'  => $empresa->direccion_tienda ?? '',
            'celular'    => $empresa->celular ?? '',
            'telefono2'  => $empresa->telefono2 ?? '',
            'celular_rl' => $empresa->celular_rl ?? '',
            'facebook'   => $parametrosMap['FB'] ?? '#',
            'instagram'  => $parametrosMap['IG'] ?? '#',
            'twitter'    => $parametrosMap['TW'] ?? '#',
            'youtube'    => $parametrosMap['YT'] ?? '#',

            // arreglo de números para el layout
            'whatsappNumbers' => [
                'Asesor1'        => $empresa->celular ?? '',
                'Asesor2'        => $empresa->telefono2 ?? '',
                'Asesor3'        => $empresa->celular_rl ?? '',
            ],
        ]);
    }
}
