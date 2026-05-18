<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\WhishListController;


// HOME
Route::get('/', [HomeController::class, 'homeConCarrusel']);
// PERFIL
Route::middleware('auth.custom')->group(function () {
    Route::get('/profile',          [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/update',   [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// AUTH
Route::get('/login',    [LoginController::class, 'showLogin'])->name('login');
Route::post('/login',   [LoginController::class, 'login'])->name('login.post');
Route::get('/register', [LoginController::class, 'showRegister'])->name('register');
Route::post('/register',[LoginController::class, 'register'])->name('register.post');
Route::post('/logout',  [LoginController::class, 'logout'])->name('logout');
// PRODUCTOS
// CATÁLOGO Paginado
Route::get('/catalogo', [ProductsController::class, 'index'])->name('catalogo.index');
// Detalle de un producto específico
Route::get('catalogo/presentaciones/{codigo}', [ProductsController::class, 'show'])->name('products.show');

// WISHLIST - Lista de deseos
Route::middleware('auth.custom')->group(function () {
    Route::get('/wishlist',         [WhishListController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WhishListController::class, 'toggle'])->name('wishlist.toggle');
});
//Carrito
Route::middleware('auth.custom')->group(function () {
    Route::get('/cart',             [CarritoController::class, 'index'])->name('carrito.index');
    Route::post('/cart/add',        [CarritoController::class, 'add'])->name('carrito.add');
    Route::put('/cart/update',      [CarritoController::class, 'update'])->name('carrito.update');
    Route::delete('/cart/remove',   [CarritoController::class, 'remove'])->name('carrito.remove');
    Route::post('/cart/vaciar',     [CarritoController::class, 'vaciar'])->name('carrito.vaciar');
    Route::get('/cart/checkout',    [CarritoController::class, 'pagar'])->name('pedidos.pagar');
});





