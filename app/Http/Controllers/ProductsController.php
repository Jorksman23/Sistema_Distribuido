<?php

namespace App\Http\Controllers;

use App\Models\products_model;

class ProductsController extends Controller
{
    protected $productModel;

    public function __construct()
    {
        $this->productModel = new products_model();
    }


    public function index()
    {
        $products = $this->productModel->getProducts();
        return response()->json($products);
    }
    public function presentaciones(){
    $presentaciones = $this->productModel->getPresentaciones();
    return response()->json($presentaciones);
    }

    public function promociones(){
        $promociones = $this->productModel->getPromociones();
        return response()->json($promociones);
    }

}
