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
use App\Repositories\LoginRepository;
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
     protected LoginRepository $loginRepository;

    public function __construct(
        CarritoModel $carrito,
        CartRepository $cartRepository,
        CartService $cartService,
        CheckoutService $checkoutService,
        PaymentMethodService $paymentMethodService,
        PaymentService $paymentService,
        OrderRepository $orderRepository,
        CxcAuxiliarProformaService $cxcAuxiliarProformaService,
        ComprobanteService $comprobanteService,
        LoginRepository $loginRepository
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
        $this->loginRepository = $loginRepository;

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

            if($request->wantsJson() || $request->ajax()){
                return response()->json([
                    'success' => true,
                    'message' => '¡Producto agregado al carrito!',
                    'carrito_count' => $this->cartRepository->count((string) session ('user_id')),
                ]);
            }
            return back()->with('success_cart','¡Producto agregado al carrito!'
            );
        } catch (Throwable $e) {
            if($request->wantsJson() || $request->ajax()){
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
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
            if ($request->wantsJson() || $request->ajax()) {
                $resumen = $this->cartService->obtenerResumenCarrito((string) session('user_id'));
                return response()->json([
                    'success'       => true,
                    'cantidad'      => (int) $request->cantidad,
                    'subtotal'      => $resumen['subtotal'],
                    'iva'           => $resumen['iva'],
                    'total'         => $resumen['total'],
                    'carrito_count' => $this->cartRepository->count((string) session('user_id')),
                ]);
            }
            return back();
        } catch (Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
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

            if ($request->wantsJson() || $request->ajax()) {
                $resumen = $this->cartService->obtenerResumenCarrito((string) session('user_id'));
                return response()->json([
                    'success'       => true,
                    'subtotal'      => $resumen['subtotal'],
                    'iva'           => $resumen['iva'],
                    'total'         => $resumen['total'],
                    'carrito_count' => $this->cartRepository->count((string) session('user_id')),
                ]);
            }
            return back();
        } catch (Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // === Vaciar Carrito ===
    public function vaciar(Request $request){
        try {
        $this->cartService->vaciarCarrito((string) session('user_id'));
        session()->forget('carrito_count');
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'       => true,
                'carrito_count' => 0,
            ]);
        }
        return redirect()->route('carrito.index');
        } catch (Throwable $e) {
          if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
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
            $usuario = $this->loginRepository->findById($codCliente);
            return view('pedidos.pagar', [...$checkout,'formasPago' => $formasPago,'usuario' => $usuario,]);
        } catch (Throwable $e) {
            return view('errors.500', ['mensaje' => 'Error al preparar pago: ' . $e->getMessage(),]);
        }
    }


    public function obtenerDatosCliente(Request $request, PaymentService $paymentService)
    {
        $cedula  = $request->get('cedula');
        $empresa = currentCompany();

        $data = [
            'cedula'     => $cedula,
            'nombre'     => $request->get('nombre'),
            'email'      => $request->get('email'),
            'telefono'   => $request->get('telefono'),
            'direccion'  => $request->get('direccion'),
            'observacion'=> $request->get('observacion'),
        ];

        //Llamar al servicio y obtener todos los datos del cliente
        $clienteData = $paymentService->obtenerOCrearCliente($data, $empresa);

        //Devolver los datos reales del cliente al frontend
        return response()->json($clienteData);
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
        'metodo_entrega' => 'required|string|in:R,E',
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
            'observacion',
            'metodo_entrega'
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
    // 1. Validar archivo comprobante
    $request->validate([
        'comprobante' => 'required|file|mimes:jpg,jpeg,png|max:5120'
    ]);

    // 2. Recuperar datos de checkout desde sesión
    $checkoutData = session('checkout_data');
    if (!$checkoutData) {
        return redirect()->route('pedidos.pagar')->withErrors([
            'error' => 'No existen datos de checkout.'
        ]);
    }

    // 3. Datos del cliente y empresa
    $codCliente = (string) session('user_id');
    $empresa    = currentCompany();

    // 4. Validar carrito
    $items = $this->carrito->getCarritoByUser($codCliente);
    if (empty($items)) {
        // Si el carrito está vacío pero checkout_data existe,
        // el comprobante ya fue procesado — redirigir a pedidos
        return redirect()->route('profile.orders')->with(
            'success',
            'Comprobante enviado correctamente.'
        );
    }

    // 5. Obtener total
    $checkout  = $this->checkoutService->obtenerCheckout($codCliente);
    $granTotal = (float) $checkout['total'];

    // 6. Guardar comprobante y redirigir a pedidos (manejado por el servicio)
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

        $formaPago = $this->paymentMethodService->obtenerFormaPago(
            (int) $orden->tipo_pago,
            currentCompany()
        );

        // Calcular subtotal e IVA con la misma lógica de CartService
        $empresa = currentCompany();

        $ivaConfig = \Illuminate\Support\Facades\DB::connection('odbc')->selectOne("
            SELECT TOP 1 parametro FROM DBA.GE_PARAMETROS
            WHERE empresa = ? AND codigo = 17
        ", [$empresa]);
        $porcentajeIva = (float) trim($ivaConfig->parametro ?? '0');

        $modoIvaConfig = \Illuminate\Support\Facades\DB::connection('odbc')->selectOne("
            SELECT TOP 1 parametro FROM DBA.web_ge_parametros
            WHERE empresa = ? AND codigo = 248
        ", [$empresa]);
        $trabajaConIvaIncluido = strtoupper(trim($modoIvaConfig->parametro ?? 'S'));

        $subtotal = 0;
        $ivaTotal = 0;

        foreach ($items as $item) {
            $precioLinea = (float) $item->pvp3 * (int) $item->cantidad;

            if (($item->iva ?? 'N') === 'S') {
                if ($trabajaConIvaIncluido === 'S') {
                    $subtotal += $precioLinea;
                    $ivaTotal += $precioLinea * ($porcentajeIva / 100);
                } else {
                    $subtotalLinea = $precioLinea / (1 + ($porcentajeIva / 100));
                    $subtotal      += $subtotalLinea;
                    $ivaTotal      += $precioLinea - $subtotalLinea;
                }
            } else {
                $subtotal += $precioLinea;
            }
        }

        $pdf = Pdf::loadView('pdf.pedido-efectivo', compact('orden', 'items', 'formaPago') + [
            'subtotal'      => number_format($subtotal, 2, '.', ''),
            'ivaTotal'      => number_format($ivaTotal, 2, '.', ''),
            'porcentajeIva' => $porcentajeIva,
        ]);

        return $pdf->download('Pedido_' . $orden->codigo . '.pdf');
    }

}
