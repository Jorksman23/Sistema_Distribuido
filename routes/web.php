<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\WhishListController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\VerificationController;
use Illuminate\Http\Request;


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
    Route::get('/cart/pagar',       [CarritoController::class, 'pagar'])->name('pedidos.pagar');
    Route::post('/carrito/procesar-pago', [CarritoController::class, 'procesarPago'])->name('carrito.procesar.pago');
});
<<<<<<< Updated upstream
Route::get('/pedidos/verp/{documento}', function($documento) {return view('pedidos.verp', ['documento' => $documento]);})->name('pedidos.verp');
Route::get('/profile/pedidos', [ProfileController::class, 'pedidos'])->name('profile.pedidos');

// PASSWORD RESET
Route::middleware('guest')->group(function () {
    Route::get('/password/request', [PasswordController::class, 'requestForm'])->name('password.request.form');
    Route::post('/password/send',   [PasswordController::class, 'sendLink'])->name('password.send.link');
    Route::get('/password/sent', [PasswordController::class, 'sent'])->name('password.sent');
    Route::get('/password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/password/reset',  [PasswordController::class, 'reset'])->name('password.reset.submit');
});

Route::get('/email/verify', function () {return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['signed'])
    ->name('verification.verify');

=======
//Route::get('/pedidos/verp/{documento}', function($documento) {return view('pedidos.verp', ['documento' => $documento]);})->name('pedidos.verp');
//Route::get('/profile/pedidos', [ProfileController::class, 'pedidos'])->name('profile.pedidos');

//Checkout
Route::get('/formas-pago/{secuencia}/cuenta-banco', [CarritoController::class,'obtenerCuentaBanco'])->name('formas-pago.cuenta');
// Comprobante de pago
Route::middleware('auth.custom')->group(function () {
    Route::get('/pedido/comprobante',[CarritoController::class, 'mostrarComprobante'])->name('pedidos.comprobante');
    Route::post('/pedido/comprobante',[CarritoController::class, 'guardarComprobante'])->name('pedidos.comprobante.guardar');
});
>>>>>>> Stashed changes

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Se envió un nuevo enlace de verificación.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
