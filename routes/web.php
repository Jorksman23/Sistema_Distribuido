<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ProfileController;

// HOME
Route::get('/', [HomeController::class, 'viewHome']);


//Login_Controller
// Route::post('/register', [LoginController::class, 'register']);
// Route::post('/login', [LoginController::class, 'login']);
// Route::middleware('auth.token')->get('/profile', function (Request $request) {
//     return response()->json([
//         'usuario' => $request->attributes->get('user')
//     ]);
// });
// GET muestra el formulario, POST lo procesa
// Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
// Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// AUTH
Route::get('/login',    [LoginController::class, 'showLogin'])->name('login');
Route::post('/login',   [LoginController::class, 'login'])->name('login.post');
Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
Route::post('/register',[LoginController::class, 'register'])->name('register.post');
Route::post('/logout',  [LoginController::class, 'logout'])->name('logout');
// PERFIL
Route::middleware('auth.custom')->group(function () {
    Route::get('/profile',          [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/update',   [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});
// CARRITO


// PRODUCTOS
// CATÁLOGO Paginado
Route::get('/catalogo', [ProductsController::class, 'index'])->name('catalogo.index');
//Eliminar despues esta ruta comentada
//Route::get('/catalogo/search', [ProductsController::class, 'indexSearch'])->name('catalogo.search');
// Listado de productos
Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
// Detalle de un producto específico

Route::get('/presentaciones/{codigo}', [ProductsController::class, 'show'])->name('products.show');


//Actualizar perfil
Route::middleware('auth.token')->put('/profile', [LoginController::class, 'updateProfile']);

// CARRITO
// Mostrar carrito del usuario
Route::get('/carrito/{userId}', [CarritoController::class, 'index'])->name('carrito.index');

// Agregar producto al carrito
Route::post('/carrito/add', [CarritoController::class, 'add'])->name('carrito.add');

// Actualizar cantidad de un producto
Route::put('/carrito/update', [CarritoController::class, 'update'])->name('carrito.update');

// Eliminar producto del carrito
Route::delete('/carrito/remove', [CarritoController::class, 'remove'])->name('carrito.remove');


