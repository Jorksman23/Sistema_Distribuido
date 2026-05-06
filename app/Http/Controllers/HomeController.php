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

    public function viewHome(Request $request)
    {
        $empresa   = $request->query('empresa', currentCompany());

        // Traemos hasta 4 productos destacados directamente desde ProductsModel
        $productos = (new ProductsModel())->getActiveProducts(4, $empresa);

        return view('Home.Home', [
            'empresa'   => $empresa,
            'productos' => $productos,
        ]);
    }
}


    // public function viewHome()
    // {
    //     return view('Home.Home');
    // }

    // public function viewHome(){
    //     return view('Home.Home', [
    //         'empresa' => Empresa::getNombre()
    //     ]);
    // }


