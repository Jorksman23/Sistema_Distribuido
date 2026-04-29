<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CarritoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/products', [ProductsController::class, 'index']);

Route::get('/carrito/{userId}', [CarritoController::class, 'index']);
Route::post('/carrito/add', [CarritoController::class, 'add']);
Route::put('/carrito/update', [CarritoController::class, 'update']);
Route::delete('/carrito/remove', [CarritoController::class, 'remove']);