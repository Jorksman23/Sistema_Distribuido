<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductsModel;
use App\Repositories\ProductRepository;

class HomeController extends Controller
{
    //Método nuevo para el carrusel
    public function homeConCarrusel(Request $request){
        $empresa    = config('app.company_code', '001');
        $repository = new ProductRepository();
        $model      = new ProductsModel();
        $productos = array_map(fn($r) => $model->mapRowToInstance($r),$repository->getActiveProducts(4, $empresa));
        $carrusel = array_map(fn($r) => $model->mapRowToInstance($r),$repository->getProductosDestacados(16, $empresa));

        return view('home.home', [
            'empresa'   => $empresa,
            'productos' => $productos,
            'carrusel'  => $carrusel,
        ]);
    }
}
