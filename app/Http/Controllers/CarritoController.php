<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarritoModel;
use App\Services\CartService;
use App\Services\PaymentService;
use App\Services\CheckoutService;
use App\Services\PaymentMethodService;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Services\ComprobanteService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\CxcAuxiliarProformaService;
use Throwable;


class CarritoController {


    protected CarritoModel $carrito;
    protected CartRepository $cartRepository;
    protected CartService $cartService;
    protected CheckoutService $checkoutService;
    protected PaymentMethodService $paymentMethodService;
    protected PaymentService $paymentService;
    protected OrderRepository $orderRepository;
    protected CxcAuxiliarProformaService $cxcAuxiliarProformaService;
    protected ComprobanteService $comprobanteService;

    public function __construct(
        CarritoModel $carrito,
        CartRepository $cartRepository,
        CartService $cartService,
        CheckoutService $checkoutService,
        PaymentMethodService $paymentMethodService,
        PaymentService $paymentService,
        OrderRepository $orderRepository,
        CxcAuxiliarProformaService $cxcAuxiliarProformaService,
        ComprobanteService $comprobanteService
    ) {
        $this->carrito = $carrito;
        $this->cartRepository = $cartRepository;
        $this->cartService = $cartService;
        $this->checkoutService = $checkoutService;
        $this->paymentMethodService = $paymentMethodService;
        $this->paymentService = $paymentService;
        $this->orderRepository = $orderRepository;
        $this->cxcAuxiliarProformaService = $cxcAuxiliarProformaService;
        $this->comprobanteService = $comprobanteService;
    }

    // === Mostrar Carrito ===
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
    // === Agregar Producto Desde Catálogo ===
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

    // === Actualizar Producto ===
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

    // === Eliminar Producto ===
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

    // === Vaciar Carrito ===
    public function vaciar(){
        try {
        $this->cartService->vaciarCarrito((string) session('user_id'));
        session()->forget('carrito_count');
        return redirect()->route('carrito.index');
        } catch (Throwable $e) {
        return back()->withErrors(['error' => $e->getMessage()]);
    }

    }
    // === Pagar ===
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


     public function obtenerDatosCliente(Request $request, CheckoutService $checkoutService)
    {
        $cedula = $request->get('cedula');
        $dataFinal = $checkoutService->obtenerDatosCliente($cedula);

        return response()->json($dataFinal);
    }

    // === Procesar Pago ===
    public function procesarPago(Request $request)
    {
    $codCliente = (string) session('user_id');
    $empresa    = currentCompany();

    // Validación de campos
    $request->validate([
        'tipo_pago'   => 'required|integer',
        'cedula'      => 'required|string|max:15',
        'nombre'      => 'required|string|max:180',
        'email'       => 'required|email|max:250',
        'telefono'    => 'required|string|max:15',
        'direccion'   => 'required|string|max:500',
        'observacion' => 'nullable|string|max:500',
    ]);

    // Delegar al servicio: él mismo llama a procesarPagoEfectivo si corresponde
    return $this->paymentService->procesarPago(
        $request->only([
            'tipo_pago',
            'cedula',
            'nombre',
            'email',
            'telefono',
            'direccion',
            'observacion'
        ]),
        $codCliente,
        $empresa
    );
    }

    // === Cuentas Banco ===
    public function obtenerCuentaBanco(int $secuencia){
    $empresa = currentCompany();
    $formaPago = $this->paymentMethodService->obtenerFormaPago($secuencia, currentCompany());
    if (!$formaPago) {
        return response()->json([
            'success' => false
        ], 404);
    }
    $cuentaBanco = $this->paymentMethodService->obtenerCuentaBanco($formaPago, currentCompany());
    if (!$cuentaBanco) {
        return response()->json([
        'success' => false,
        'message' => 'Esta forma de pago no requiere datos bancarios.'
        ]);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'descripcion'   => $cuentaBanco->descripcion ?? '',
            'cuenta'        => $cuentaBanco->cuenta ?? '',
            'tipo'          => $cuentaBanco->tipo ?? '',
            'cta_contable'  => $cuentaBanco->cta_contable ?? '',
        ]
    ]);
    }

    // === Comprobante Pago ===
    public function mostrarComprobante()
    {
    return $this->comprobanteService->mostrarComprobante(session('checkout_data'));
    }

    // === Guardar Comprobante ===
    public function guardarComprobante(Request $request)
    {
    $request->validate([
        'comprobante' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120'
    ]);

    $checkoutData = session('checkout_data');
    if (!$checkoutData) {
        return redirect()->route('pedidos.pagar')->withErrors([
            'error' => 'No existen datos de checkout.'
        ]);
    }

    $codCliente = (string) session('user_id');
    $empresa    = currentCompany();
    $items      = $this->carrito->getCarritoByUser($codCliente);
    if (empty($items)) {
        return redirect()->route('carrito.index')->withErrors([
            'error' => 'El carrito está vacío.'
        ]);
    }

    $checkout   = $this->checkoutService->obtenerCheckout($codCliente);
    $granTotal  = (float) $checkout['total'];

    return $this->comprobanteService->guardarComprobante(
        $request->file('comprobante'),
        $checkoutData,
        $codCliente,
        $empresa,
        $items,
        $granTotal
    );
    }

    // === Descargar Pedido ===
    public function descargarPedido($codigo) {
        $orden = $this->paymentMethodService->obtenerOrden($codigo, currentCompany());

        if (!$orden) {
            abort(404);
        }

        $items = $this->orderRepository->obtenerItemsOrden(
            $codigo,
            (string) session('user_id')
        );

        //Obtener forma de pago
        $formaPago = $this->paymentMethodService->obtenerFormaPago(
            (int) $orden->tipo_pago,
            currentCompany()
        );

        $pdf = Pdf::loadView('pdf.pedido-efectivo', compact('orden', 'items', 'formaPago'));

        return $pdf->download('Pedido_' . $orden->codigo . '.pdf');
    }

}
