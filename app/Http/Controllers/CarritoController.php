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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CarritoController extends Controller
{

    protected CarritoModel $carrito;
    protected CartRepository $cartRepository;
    protected CartService $cartService;
    protected CheckoutService $checkoutService;
    protected PaymentMethodService $paymentMethodService;
    protected PaymentService $paymentService;
    protected OrderRepository $orderRepository;

    public function __construct(
        CarritoModel $carrito,
        CartRepository $cartRepository,
        CartService $cartService,
        CheckoutService $checkoutService,
        PaymentMethodService $paymentMethodService,
        PaymentService $paymentService,
        OrderRepository $orderRepository
    ) {
        $this->carrito = $carrito;
        $this->cartRepository = $cartRepository;
        $this->cartService = $cartService;
        $this->checkoutService = $checkoutService;
        $this->paymentMethodService = $paymentMethodService;
        $this->paymentService = $paymentService;
        $this->orderRepository = $orderRepository;
    }

    // === Mostrar carrito ===
    public function index()
    {
        $codCliente = (string) session('user_id');

        try {
            $resumen = $this->cartService->obtenerResumenCarrito($codCliente);
            return view('cart.index', $resumen);
        } catch (Throwable $e) {
            // return view('errors.500', [
            //     'mensaje' => 'Error al obtener carrito: ' . $e->getMessage(),
            // ]);
            dd([
            'mensaje' => $e->getMessage(),
            'archivo' => $e->getFile(),
            'linea'   => $e->getLine(),
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
        try {
            $this->paymentService->procesarPago([
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
         // Guardar temporalmente datos del checkout
        session([
            'checkout_data' => [
                'empresa'     => $empresa,
                'tipo_pago'   => $request->tipo_pago,
                'cedula'      => $request->cedula,
                'nombre'      => $request->nombre,
                'email'       => $request->email,
                'telefono'    => $request->telefono,
                'direccion'   => $request->direccion,
                'observacion' => $request->observacion,
            ]
        ]);
            return redirect()->route('pedidos.comprobante')->with(
                'success',
                'Ahora suba su comprobante de pago.'
            );
        } catch (Throwable $e) {
            return back()->withErrors([
                'error' => 'Error al procesar la compra: ' . $e->getMessage()
            ]);
        }
    }

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

    public function mostrarComprobante(){
        $checkoutData = session('checkout_data');
        if (!$checkoutData) {
            return redirect()
                ->route('pedidos.pagar')
                ->withErrors([
                    'error' => 'No hay datos de pago.'
                ]);
        }
        $formaPago = $this->paymentMethodService->obtenerFormaPago(
            $checkoutData['tipo_pago'],
            currentCompany()
        );
        $cuentaBanco = $this->paymentMethodService->obtenerCuentaBanco(
            $formaPago,
            currentCompany()
        );

        return view('pedidos.comprobante', [
            'formaPago'   => $formaPago,
            'cuentaBanco' => $cuentaBanco,
        ]);
    }

    public function guardarComprobante(Request $request){
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
        $items = $this->carrito->getCarritoByUser($codCliente);
        if (empty($items)) {
            return redirect()->route('carrito.index')->withErrors([
                'error' => 'El carrito está vacío.'
            ]);
        }
        // Generar código de orden
        $codigoOrden = $this->orderRepository->generarCodigoOrden($empresa);
        // Total carrito con IVA
        $checkout = $this->checkoutService->obtenerCheckout($codCliente);
        $granTotal = (float) $checkout['total'];
        // Guardar archivo físico
        $archivo = $request->file('comprobante');
        $nombreArchivo = $codigoOrden . '_' . time() . '.' .$archivo->getClientOriginalExtension();
        $ruta = $archivo->storeAs(
            'comprobantes/' . $empresa,
            $nombreArchivo,
            'public'
        );
        try {

            DB::connection('odbc')->beginTransaction();
            // Crear orden
            DB::connection('odbc')
                ->table('DBA.PW_ORDENES_WEB')
                ->insert([
                    'codigo'             => $codigoOrden,
                    'cod_cliente'        => $codCliente,
                    'n_documento'        => $codigoOrden,
                    'tipo'               => companyDefaultOrderType('invoice'),
                    'empresa'            => $empresa,
                    'uuid_session'       => md5(uniqid(rand(), true)),
                    'tipo_pago'          => $checkoutData['tipo_pago'],
                    'items_carrito'      => count($items),
                    'gran_total'         => $granTotal,
                    'estatus'            => '1',
                    'cedula_cliente'     => $checkoutData['cedula'],
                    'nombre_cliente'     => $checkoutData['nombre'],
                    'email_cliente'      => $checkoutData['email'],
                    'telefono_cliente'   => $checkoutData['telefono'],
                    'direccion_cliente'  => $checkoutData['direccion'],
                    'observacion_compra' => $checkoutData['observacion'] ?? null,
                    'fecha_creacion'     => now(),
                    'fecha_modificacion' => now(),
                ]);
            // Adjunto
            DB::connection('odbc')
                ->table('DBA.PW_ADJUNTO_WEB')
                ->insert([
                    'empresa'         => $empresa,
                    'cod_orden'       => $codigoOrden,
                    'cod_cliente'     => $codCliente,
                    'foto'            => $ruta,
                    'foto_id'         => $nombreArchivo,
                    'nombre_archivo'  => $archivo->getClientOriginalName(),
                    'tipo_archivo'    => $archivo->getClientOriginalExtension(),
                    'created_at'      => now(),
                    'update_at'       => now(),
                ]);
            // Historial
            DB::connection('odbc')
                ->table('DBA.PW_HISTORICO_PEDIDO')
                ->insert([
                    'cod_orden'      => $codigoOrden,
                    'codigo_cliente' => $codCliente,
                    'cod_estado'     => '2',
                    'observacion'    => 'Comprobante cargado por el cliente',
                    'fecha_cambio'   => now(),
                    'created_at'     => now(),
                    'update_at'      => now(),
                    'empresa'        => $empresa,
                ]);
            // Asociar carrito a la orden
            $this->cartRepository->marcarComoProcesado($codCliente,$codigoOrden);
            DB::connection('odbc')->commit();
            // Limpiar sesión checkout
            session()->forget('checkout_data');
            return redirect()->route('profile.show')->with(
                'success',
                'Comprobante enviado correctamente.'
            );
        } catch (\Throwable $e) {
            DB::connection('odbc')->rollBack();
            // Eliminar archivo físico si la BD falló
            if (!empty($ruta)) {
                Storage::disk('public')->delete($ruta);
            }
            return back()->withErrors([
                'error' => 'Error al guardar el comprobante: ' . $e->getMessage()
            ]);
        }
    }
}
