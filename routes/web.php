<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;

// HOME
Route::get('/', [HomeController::class, 'viewHome']);

//Proteger rutas futuras con middleware por ejemplo perfil, pedidos,cuenta, pagos, lista de deseos,etc
Route::middleware('auth.custom')->group(function () {

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

});


// AUTH
Route::get('/login',    [LoginController::class, 'showLogin'])->name('login');
Route::post('/login',   [LoginController::class, 'login'])->name('login.post');
Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
Route::post('/register',[LoginController::class, 'register'])->name('register.post');
Route::post('/logout',  [LoginController::class, 'logout'])->name('logout');

// CARRITO


// PRODUCTOS
Route::get('/products', [ProductsController::class, 'index']);

//Actualizar perfil
Route::middleware('auth.token')->put('/profile', [LoginController::class, 'updateProfile']);


