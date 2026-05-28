<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarritoModel;
use App\Services\CartService;
use App\Services\PaymentService;
use App\Services\CheckoutService;
use App\Services\PaymentMethodService;
use App\Repositories\CartRepository;
use Throwable;

class CarritoController extends Controller
{

    protected CarritoModel $carrito;
    protected CartRepository $cartRepository;
    protected CartService $cartService;
    protected CheckoutService $checkoutService;
    protected PaymentMethodService $paymentMethodService;
    protected PaymentService $paymentService;

    public function __construct(
        CarritoModel $carrito,
        CartRepository $cartRepository,
        CartService $cartService,
        CheckoutService $checkoutService,
        PaymentMethodService $paymentMethodService,
        PaymentService $paymentService
    ) {
        $this->carrito = $carrito;
        $this->cartRepository = $cartRepository;
        $this->cartService = $cartService;
        $this->checkoutService = $checkoutService;
        $this->paymentMethodService = $paymentMethodService;
        $this->paymentService = $paymentService;
    }

    // === Mostrar carrito ===
    public function index()
    {
        $codCliente = (string) session('user_id');

        try {
            $resumen = $this->cartService->obtenerResumenCarrito($codCliente);
            return view('cart.index', $resumen);
        } catch (Throwable $e) {
            return view('errors.500', [
                'mensaje' => 'Error al obtener carrito: ' . $e->getMessage(),
            ]);
        }
    }
    // === Agregar producto desde catálogo ===
    public function add(Request $request){
        $request->validate([
            'codigo_item'  => 'required|string',
            'nombre'       => 'nullable|string',
            'pvp3'         => 'nullable|numeric',
            'imagen'       => 'nullable|string',
            'presentacion' => 'nullable|integer',
        ]);
        try {
            $this->cartService->agregarProducto($request->all(),(string) session('user_id'));
            session()->forget('carrito_count');
            return back()->with('success_cart','¡Producto agregado al carrito!'
            );
        } catch (Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request){
        $request->validate([
            'id_item_web' => 'required|integer',
            'cantidad'    => 'required|integer|min:1|max:99',
        ]);
        try {
            $this->cartService->actualizarCantidad($request->id_item_web,$request->cantidad,(string) session('user_id'));
            session()->forget('carrito_count');
            return back();
        } catch (Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // === Eliminar producto ===
    public function remove(Request $request){
    $request->validate([
        'id_item_web' => 'required|integer',
    ]);
    try {
        $this->cartService->eliminarProducto($request->id_item_web,(string) session('user_id'));
        session()->forget('carrito_count');
        return back();
    } catch (Throwable $e) {
        return back()->withErrors(['error' => $e->getMessage()]);
    }
}

    // === Vaciar carrito ===
    public function vaciar(){
        try {
        $this->cartService->vaciarCarrito((string) session('user_id'));
        session()->forget('carrito_count');
        return redirect()->route('carrito.index');
        } catch (Throwable $e) {
        return back()->withErrors(['error' => $e->getMessage()]);
    }

    }

   public function pagar(){
        $codCliente = (string) session('user_id');
        try {
            $checkout = $this->checkoutService->obtenerCheckout($codCliente);
            if (empty($checkout['items'])) {
                return redirect()->route('carrito.index')->withErrors(['error' => 'El carrito está vacío']);
            }
            $formasPago = $this->paymentMethodService->obtenerFormasPago(currentCompany());
            return view('pedidos.pagar', [...$checkout,'formasPago' => $formasPago,]);
        } catch (Throwable $e) {
            return view('errors.500', ['mensaje' => 'Error al preparar pago: ' . $e->getMessage(),]);
        }
    }

    // === PROCESAR PAGO ===
    public function procesarPago(Request $request){
        $codCliente = (string) session('user_id');
        $empresa    = currentCompany();

        $request->validate([
            'tipo_pago' => 'required|integer',
            'cedula'    => 'required|string|max:15',
            'nombre'    => 'required|string|max:180',
            'email'     => 'required|email|max:250',
            'telefono'  => 'required|string|max:15',
            'direccion' => 'required|string|max:500',
            'observacion' => 'nullable|string|max:500',
        ]);

        $formaPago = $this->paymentMethodService->obtenerFormaPago($request->tipo_pago,$empresa);

        if (!$formaPago) {
            return back()->withErrors(['error' => 'Forma de pago inválida']);
        }
        $cuentaBanco = $this->paymentMethodService->obtenerCuentaBanco($formaPago,$empresa);
        try {
            $codigoOrden = $this->paymentService->procesarPago([
                'tipo_pago'   => $request->tipo_pago,
                'cedula'      => $request->cedula,
                'nombre'      => $request->nombre,
                'email'       => $request->email,
                'telefono'    => $request->telefono,
                'direccion'   => $request->direccion,
                'observacion' => $request->observacion,
            ],
            $codCliente,
            $empresa
        );

            return redirect()->route('pedidos.verp', [
                'documento' => $codigoOrden
            ])->with('success', '¡Compra realizada exitosamente!');

        } catch (Throwable $e) {
            return back()->withErrors([
                'error' => 'Error al procesar la compra: ' . $e->getMessage()
            ]);
        }
    }

}
