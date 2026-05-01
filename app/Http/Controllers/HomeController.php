<?php

namespace App\Http\Controllers;

use App\Models\login_model;
use App\Models\Empresa;
class HomeController extends Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = new login_model();
    }

    public function viewHome()
    {
        return view('Home.Home');
    }
    // public function viewHome(){
    //     return view('Home.Home', [
    //         'empresa' => Empresa::getNombre()
    //     ]);
    // }
}
