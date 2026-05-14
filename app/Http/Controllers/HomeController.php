<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductsModel;


class HomeController extends Controller
{

    // Método nuevo para el carrusel
    public function homeConCarrusel(Request $request){
        $empresa  = config('app.company_code', '001');
        $model    = new ProductsModel();

        $productos = $model->getActiveProducts(4, $empresa);
        $carrusel  = $model->getProductosDestacados(16, $empresa);

        return view('home.home', [
            'empresa'   => $empresa,
            'productos' => $productos,
            'carrusel'  => $carrusel,
        ]);
    }
}

