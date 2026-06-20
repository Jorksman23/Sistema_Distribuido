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
use App\Services\OrderApprovalService;


// HOME
Route::get('/', [HomeController::class, 'homeConCarrusel']);
// PERFIL
Route::middleware('auth.custom')->group(function () {
    Route::get('/profile',          [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/mis-pedidos',      [ProfileController::class, 'orders'])->name('profile.orders');
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
//CARTS
Route::middleware('auth.custom')->group(function () {
    Route::get('/cart',             [CarritoController::class, 'index'])->name('carrito.index');
    Route::post('/cart/add',        [CarritoController::class, 'add'])->name('carrito.add');
    Route::put('/cart/update',      [CarritoController::class, 'update'])->name('carrito.update');
    Route::delete('/cart/remove',   [CarritoController::class, 'remove'])->name('carrito.remove');
    Route::post('/cart/vaciar',     [CarritoController::class, 'vaciar'])->name('carrito.vaciar');
    Route::get('/cart/pagar',       [CarritoController::class, 'pagar'])->name('pedidos.pagar');
    Route::post('/carrito/procesar-pago', [CarritoController::class, 'procesarPago'])->name('carrito.procesar.pago');
});

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

//EMAIL VERIFY
Route::get('/email/verify', function () {return view('auth.verify-email');})
    ->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['signed'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [LoginController::class, 'resendVerification'])
    ->name('verification.send');


//Checkout
Route::get('/formas-pago/{secuencia}/cuenta-banco', [CarritoController::class,'obtenerCuentaBanco'])->name('formas-pago.cuenta');
// Comprobante de pago
Route::middleware('auth.custom')->group(function () {
    Route::get('/pedido/comprobante',[CarritoController::class, 'mostrarComprobante'])->name('pedidos.comprobante');
    Route::post('/pedido/comprobante',[CarritoController::class, 'guardarComprobante'])->name('pedidos.comprobante.guardar');
});
//Descargar pedido
Route::get('/pedidos/{codigo}/descargar',[CarritoController::class, 'descargarPedido'])->name('pedidos.descargar');

//Prueba tecnica aprobación de pedidos http://127.0.0.1:8000/test-aprobar/000124
Route::get('/test-aprobar/{codigo}', function ($codigo,OrderApprovalService $service) {return $service->aprobar($codigo,currentCompany());});
//Obtener los cliente a traves de un servicio
Route::get('/cliente/datos', [CarritoController::class, 'obtenerDatosCliente'])->name('cliente.datos');
