<?php
use Illuminate\Http\Request;
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
Route::post('/register', [LoginController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth.token')->get('/profile', function (Request $request) {
    return response()->json([
        'usuario' => $request->attributes->get('user')
    ]);
});

// Obtener usuarios
Route::get('/users', [LoginController::class, 'users']);


