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
    // public function boot(): void
    // {
    //     //
    // public function boot(): void{
    //     View::share('empresa', Empresa::getNombre());
    // }

    public function boot(): void
    {
        View::share('empresaNombre', Empresa::getNombre());
    }

}
