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
}
