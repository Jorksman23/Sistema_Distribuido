<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarritoModel;

class CarritoController extends Controller
{
    protected CarritoModel $carrito;

    public function __construct()
    {
        $this->carrito = new CarritoModel();
    }
}
