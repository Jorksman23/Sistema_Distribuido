<?php

namespace App\Http\Controllers;

use App\Models\login_model;
use App\Models\products_model;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\ProductCatalog as ModelsProductCatalog;

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
    $productos = (new ModelsProductCatalog())->getCatalog(4, $empresa); // 4 destacados

    return view('Home.Home', [
        'empresa'   => $empresa,
        'productos' => $productos,
    ]);
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

}
