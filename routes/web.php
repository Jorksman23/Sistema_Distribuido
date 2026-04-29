<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\LoginController;

Route::get('/', function () {
    return view('welcome');
});
//Prueba obtenemos productos
Route::get('/products', [ProductsController::class, 'index']);

Route::get('/carrito/{userId}', [CarritoController::class, 'index']);
Route::post('/carrito/add', [CarritoController::class, 'add']);
Route::put('/carrito/update', [CarritoController::class, 'update']);
Route::delete('/carrito/remove', [CarritoController::class, 'remove']);




//Login_Controller
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [LoginController::class, 'register']);
// Obtener usuarios
Route::get('/users', [LoginController::class, 'users']);