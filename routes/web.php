<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;


// Route::get('/login-view', function () {
//     return view('login.login');
// });
//Route::get('/', [HomeController::class, 'viewHome']);
Route::get('/', [HomeController::class, 'viewHome']);
Route::get('/home', [HomeController::class, 'index']);

//Prueba obtenemos productos
Route::get('/products', [ProductsController::class, 'index']);

Route::get('/carrito/{userId}', [CarritoController::class, 'index']);
Route::post('/carrito/add', [CarritoController::class, 'add']);
Route::put('/carrito/update', [CarritoController::class, 'update']);
Route::delete('/carrito/remove', [CarritoController::class, 'remove']);

//Login_Controller
// Route::post('/register', [LoginController::class, 'register']);
// Route::post('/login', [LoginController::class, 'login']);
// Route::middleware('auth.token')->get('/profile', function (Request $request) {
//     return response()->json([
//         'usuario' => $request->attributes->get('user')
//     ]);
// });
// GET muestra el formulario, POST lo procesa
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
Route::post('/register', [LoginController::class, 'register'])->name('register.post');

// Obtener usuarios
Route::get('/users', [LoginController::class, 'users']);

//Actualizar perfil
Route::middleware('auth.token')->put('/profile', [LoginController::class, 'updateProfile']);


