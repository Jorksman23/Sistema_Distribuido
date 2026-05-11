<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\login_model;
use App\Models\ProductsModel;
use App\Models\Empresa;

class HomeController extends Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = new login_model();
    }
    // Método nuevo para el carrusel
    public function homeConCarrusel(Request $request){
        $empresa  = config('app.company_code', '001');
        $model    = new ProductsModel();

        $productos = $model->getActiveProducts(4, $empresa);
        $carrusel  = $model->getProductosDestacados(16, $empresa);

        return view('Home.Home', [
            'empresa'   => $empresa,
            'productos' => $productos,
            'carrusel'  => $carrusel,
        ]);
    }
}





