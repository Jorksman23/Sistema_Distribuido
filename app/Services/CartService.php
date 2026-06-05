<?php

namespace App\Services;

use App\Models\CarritoModel;
use App\Models\ProductsModel;
use App\Repositories\CartRepository;
use Illuminate\Support\Facades\DB;
class CartService
{
    protected CarritoModel $carrito;
    protected CartRepository $cartRepository;

    public function __construct(
        CarritoModel $carrito,
        CartRepository $cartRepository
    ) {
        $this->carrito = $carrito;
        $this->cartRepository = $cartRepository;
    }

        public function agregarProducto(array $data, string $codCliente): void {
        $presentacion = (int) ($data['presentacion'] ?? 0);
        if ($this->cartRepository->exists($codCliente, $data['codigo_item'], $presentacion, $data['ubicacion'] ?? null)) {
            $item = $this->carrito->getItemByProducto($codCliente, $data['codigo_item'], $presentacion);
            if ($item) {
                $stockDisponible = $this->carrito->getStockDisponible(
                    $item->codigo_item,
                    $item->presentacion,
                    currentCompany(),
                    $item->ubicacion
                );
                if ($item->cantidad >= $stockDisponible) {
                    throw new \Exception(
                        "No hay más stock disponible para este producto en esta ubicación."
                    );
                }
                $this->cartRepository->updateCantidad($item->id_item_web, $codCliente, $item->cantidad + 1);
            }
            return;
        }
        $producto = (new ProductsModel())->findByCodigo($data['codigo_item'], currentCompany());
        if (!$producto) {
            throw new \Exception('Producto no encontrado');
        }

        $nombre = $data['nombre'] ?? $producto->descripcion1;
        $pvp3   = $data['pvp3']   ?? $producto->pvp1;
        $imagen = $data['imagen'] ?? $producto->imagen;
        $this->carrito->add([
            'codigo_item'  => $data['codigo_item'],
            'nombre'       => ProductsModel::cleanString($nombre),
            'costo_real'   => $pvp3,
            'pvp3'         => $pvp3,
            'cantidad'     => 1,
            'cod_cliente'  => $codCliente,
            'imagen'       => $imagen,
            'iva'          => $producto->iva ?? 'N',
            'presentacion' => $presentacion,
            'ubicacion'    => $data['ubicacion'] ?? null,
        ]);
    }
   public function actualizarCantidad(int $idItemWeb,int $cantidad,string $codCliente): void {
        $item = $this->carrito->getItemById($idItemWeb, $codCliente);
        if (!$item) {
            throw new \Exception('Producto no encontrado en carrito');
        }
        $stockDisponible = $this->carrito->getStockDisponible(
                $item->codigo_item,
                $item->presentacion,
                currentCompany()
            );
        if ($cantidad > $stockDisponible) {
            throw new \Exception('Solo hay ' .(int)$stockDisponible .' unidades disponibles');
        }
        $this->cartRepository->updateCantidad(
            $idItemWeb,
            $codCliente,
            $cantidad
        );
    }

    public function eliminarProducto(int $idItemWeb,string $codCliente): void {
        $this->cartRepository->delete(
            $idItemWeb,
            $codCliente
        );
    }

    public function vaciarCarrito(string $codCliente): void {
        $this->cartRepository->vaciar($codCliente);
    }

    public function obtenerResumenCarrito(string $codCliente): array{
        $items = $this->carrito->getCarritoByUser($codCliente);
        $subtotal = 0;
        $ivaTotal = 0;
        $ivaConfig = DB::connection('odbc')
            ->select("SELECT TOP 1 * FROM DBA.GE_PARAMETROS WHERE empresa = ? AND codigo = 17", [currentCompany()]);

            //Cambios
            $porcentajeIva = (float)(isset($ivaConfig[0]) ? $ivaConfig[0]->parametro : 0);
        foreach ($items as $item) {
            $subtotalLinea =(float)$item->pvp3 *(int)$item->cantidad;
            $subtotal += $subtotalLinea;
            if (($item->iva ?? 'N') === 'S') {
                $ivaTotal += ($subtotalLinea *$porcentajeIva) / 100;
            }
        }
        $total = $subtotal + $ivaTotal;
        return [
            'items'     => $items,
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'iva'      => number_format($ivaTotal, 2, '.', ''),
            'total'    => number_format($total, 2, '.', ''),
            'count'    => count($items),
        ];
    }
}
