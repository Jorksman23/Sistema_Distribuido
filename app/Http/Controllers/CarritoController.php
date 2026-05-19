<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarritoModel;
use App\Models\ProductsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ProformaGenerator;
use App\Helpers\CompanyHelper;
use Throwable;

class CarritoController extends Controller
{
    protected $carrito;
    protected $proformaGenerator;

    public function __construct()
    {
        $this->carrito = new CarritoModel();
        $this->proformaGenerator = new ProformaGenerator();
    }

    // === Mostrar carrito ===
    public function index()
    {
        $codCliente = (string) session('user_id');

        try {
            $items = $this->carrito->getCarritoByUser($codCliente);
            $total = $this->carrito->getTotal($codCliente);
            $count = count($items);

            return view('cart.index', [
                'items' => $items,
                'total' => number_format($total, 2, '.', ''),
                'count' => $count,
            ]);
        } catch (Throwable $e) {
            return view('errors.500', [
                'mensaje' => 'Error al obtener carrito: ' . $e->getMessage(),
            ]);
        }
    }


    // === Agregar producto desde catálogo ===
    public function add(Request $request)
    {
        $request->validate([
            'codigo_item'  => 'required|string',
            'nombre'       => 'nullable|string',
            'pvp3'         => 'nullable|numeric',
            'imagen'       => 'nullable|string',
            'presentacion' => 'nullable|integer',
        ]);

        $codCliente   = (string) session('user_id');
        $presentacion = (int) ($request->presentacion ?? 0);
        //$existe = $this->carrito->exists($codCliente, $request->codigo_item, $presentacion);
        // dd([
        //     'codigo_item'  => $request->codigo_item,
        //     'presentacion' => $presentacion,
        //     'cod_cliente'  => $codCliente,
        //     'existe'       => $existe,
        // ]);
        try {
            if ($this->carrito->exists($codCliente, $request->codigo_item, $presentacion)) {
                $item = $this->carrito->getItemByProducto(
                    $codCliente,
                    $request->codigo_item,
                    $presentacion
                );
                if ($item) {
                    $this->carrito->updateCantidad(
                        $item->id_item_web,
                        $codCliente,
                        $item->cantidad +1
                    );
                }
            } else {
                $nombre = $request->nombre;
                $pvp3   = $request->pvp3;
                $imagen = $request->imagen;

                if (!$nombre || !$pvp3) {
                    $producto = (new ProductsModel())->findByCodigo(
                        $request->codigo_item,
                        currentCompany()
                    );

                    if (!$producto) {
                        return back()->withErrors(['error' => 'Producto no encontrado']);
                    }

                    $nombre = $nombre ?? $producto->descripcion1;
                    $pvp3   = $pvp3   ?? $producto->pvp1;
                    $imagen = $imagen ?? $producto->imagen;
                }
                // Limpiar encoding del nombre
                $nombre = ProductsModel::cleanString($nombre);
                $this->carrito->add([
                    'codigo_item'  => $request->codigo_item,
                    'nombre'       => $nombre,
                    'costo_real'   => $pvp3,
                    'pvp3'         => $pvp3,
                    'cantidad'     => 1,
                    'cod_cliente'  => $codCliente,
                    'imagen'       => $imagen,
                    'iva'          => 'N',
                    'presentacion' => $presentacion,
                ]);
            }

            // Limpiar caché del contador
            session()->forget('carrito_count');

        } catch (Throwable $e) {

            return back()->withErrors(['error' => 'Error al agregar: ' . $e->getMessage()]);
        }

        return back()->with('success_cart', '¡Producto agregado al carrito!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_item_web' => 'required|integer',
            'cantidad'    => 'required|integer|min:1|max:99',
        ]);

        $codCliente = (string) session('user_id');

        try {
               //Obtener el Item actual
            $item = $this->carrito->getItemById($request->id_item_web, $codCliente);
            if(!$item){
                return back()->withErrors(['error'=> 'Producto no encontrado en el carrito']);
            }
            //Verificar stock disponible
            $stockDisponible = $this->carrito->getStockDisponible(
                $item->codigo_item,
                $item->presentacion,
                currentCompany()
            );
            if($request->cantidad > $stockDisponible){
                return back()->withErrors([
                    'error' => 'Solo hay '. (int)$stockDisponible.' unidades disponibles'
                ]);
            }
            $this->carrito->updateCantidad(
                $request->id_item_web,
                $codCliente,
                $request->cantidad
            );
            session()->forget('carrito_count');

        } catch (Throwable $e) {
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);

        }

        return back();
    }

    // === Eliminar producto ===
    public function remove(Request $request)
    {
        $request->validate([
            'id_item_web' => 'required|integer',
        ]);

        $codCliente = (string) session('user_id');

        try {
            $this->carrito->remove($request->id_item_web, $codCliente);
            session()->forget('carrito_count');

        } catch (Throwable $e) {
            return back()->withErrors(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }

        return back();
    }

    // === Vaciar carrito ===
    public function vaciar()
    {
        $codCliente = (string) session('user_id');

        try {
            $this->carrito->vaciar($codCliente);
            session()->forget('carrito_count');

        } catch (Throwable $e) {
            return back()->withErrors(['error' => 'Error al vaciar: ' . $e->getMessage()]);
        }

        return redirect()->route('carrito.index');
    }

   public function pagar()
    {
        $codCliente = (string) session('user_id');

        try {
            $items = $this->carrito->getCarritoByUser($codCliente);
            $total = $this->carrito->getTotal($codCliente);
            $count = count($items);

            if (empty($items)) {
                return redirect()->route('carrito.index')
                    ->withErrors(['error' => 'El carrito está vacío']);
            }

            return view('pedidos.pagar', [
                'items' => $items,
                'total' => number_format($total, 2, '.', ''),
                'count' => $count,
            ]);
        } catch (Throwable $e) {
            return view('errors.500', [
                'mensaje' => 'Error al preparar pago: ' . $e->getMessage(),
            ]);
        }
    }

    // === PROCESAR PAGO ===
    public function procesarPago(Request $request)
    {
        $codCliente = (string) session('user_id');
        $empresa    = currentCompany();

        $request->validate([
            'tipo_pago'   => 'required|in:payphone,transferencia,contraentrega',
            'cedula'      => 'required|string|max:15',
            'nombre'      => 'required|string|max:180',
            'email'       => 'required|email|max:250',
            'telefono'    => 'required|string|max:15',
            'direccion'   => 'required|string|max:500',
            'observacion' => 'nullable|string|max:500',
        ]);

        try {
            DB::connection('odbc')->beginTransaction();

            $items = $this->carrito->getCarritoByUser($codCliente);
            if (empty($items)) {
                throw new \Exception('El carrito está vacío');
            }

            $granTotal   = $this->carrito->getTotal($codCliente);
            $codigoOrden = $this->generarCodigoOrden($empresa);

            // === MAPEO DE TIPO DE PAGO A SECUENCIA (cxc_forma_pago) ===
            $secuenciaPago = match(strtolower($request->tipo_pago)) {
                'payphone'       => 5,   // Tarjetas de Crédito
                'transferencia'  => 7,   // Transferencia Bancaria
                'contraentrega'  => 1,   // Contra Entrega / Efectivo
                default          => 1,
            };

            // 1. Insertar Orden Web
            $ordenData = [
                'codigo'            => $codigoOrden,
                'cod_cliente'       => $codCliente,
                'n_documento'       => $codigoOrden,
                'tipo'              => 'TW',
                'empresa'           => $empresa,
                'uuid_session'      => md5(uniqid(rand(), true)),
                'tipo_pago'         => $secuenciaPago,
                'items_carrito'     => count($items),
                'gran_total'        => $granTotal,
                'estatus'           => '1',
                'cedula_cliente'    => $request->cedula,
                'nombre_cliente'    => $request->nombre,
                'email_cliente'     => $request->email,
                'telefono_cliente'  => $request->telefono,
                'direccion_cliente' => $request->direccion,
                'observacion_compra'=> $request->observacion,
                'fecha_creacion'    => now(),
                'fecha_modificacion'=> now(),
            ];

            DB::connection('odbc')->table('DBA.PW_ORDENES_WEB')->insert($ordenData);

            // 2. Obtener pw_id recién creado
            $orden = DB::connection('odbc')->selectOne("
                SELECT TOP 1 pw_id
                FROM DBA.PW_ORDENES_WEB
                WHERE codigo = ? AND empresa = ?
                ORDER BY pw_id DESC
            ", [$codigoOrden, $empresa]);

            if (!$orden || empty($orden->pw_id)) {
                throw new \Exception('No se pudo recuperar el ID de la orden');
            }

            $pw_id = $orden->pw_id;

            Log::info("Orden Web creada", ['pw_id' => $pw_id, 'codigo' => $codigoOrden]);

            // 3. Marcar items del carrito como procesados
            DB::connection('odbc')->table('DBA.pw_carrito_web')
                ->where('cod_cliente', $codCliente)
                ->where('estatus', '1')
                ->update(['orden_id' => $pw_id, 'estatus' => '2']);

            // 4. Generar Proforma + Descontar Stock
            $proformaService = new ProformaGenerator();
            $documento = $proformaService->generarDesdeOrden(
                (object) ['pw_id' => $pw_id, 'codigo' => $codigoOrden, 'observacion_compra' => $request->observacion],
                $items,
                $empresa
            );

            // 5. Vaciar carrito
            $this->carrito->vaciar($codCliente);

            DB::connection('odbc')->commit();

            Log::info("Compra completada exitosamente", ['codigo' => $codigoOrden]);

            return redirect()->route('pedidos.verp', ['documento' => $codigoOrden])
                             ->with('success', '¡Compra realizada exitosamente!');

        } catch (Throwable $e) {
            DB::connection('odbc')->rollBack();
            Log::error('ERROR PROCESAR PAGO: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return back()->withErrors(['error' => 'Error al procesar la compra: ' . $e->getMessage()]);
        }
    }

    private function generarCodigoOrden(string $empresa): string
    {
        $max = DB::connection('odbc')->selectOne("
            SELECT MAX(CAST(codigo AS INTEGER)) as maxc
            FROM DBA.PW_ORDENES_WEB WHERE empresa = ?
        ", [$empresa]);

        return str_pad(($max->maxc ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }
}
