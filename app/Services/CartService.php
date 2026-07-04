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
        $ubicacion    = (string) session ('ubicacion_seleccionada','');
        if ($this->cartRepository->exists($codCliente, $data['codigo_item'], $presentacion, $ubicacion)) {
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
                        "No hay más stock disponible para este producto."
                    );
                }
                $this->cartRepository->updateCantidad($item->id_item_web, $codCliente, $item->cantidad + 1);
            }
            return;
        }
        $raw = (new \App\Repositories\ProductRepository())->findByCodigo($data['codigo_item'], currentCompany());
        if (!$raw) {
            throw new \Exception('Producto no encontrado');
        }
        $producto = (new ProductsModel())->mapRowToInstance($raw);
        $nombre = $producto->descripcion1;
        $pvp3   = $data['pvp3']   ?? $producto->pvp1;
        $imagen = $data['imagen'] ?? $producto->imagen ?? 'https://placehold.co/300x300?text=Sin+imagen';
        $this->carrito->add([
            'codigo_item'  => $data['codigo_item'],
            'nombre'       => $nombre,
            'costo_real'   => $pvp3,
            'pvp3'         => $pvp3,
            'cantidad'     => 1,
            'cod_cliente'  => $codCliente,
            'imagen'       => $imagen,
            'iva'          => $producto->iva ?? 'N',
            'presentacion' => $presentacion,
            'ubicacion'    => $ubicacion,
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
                currentCompany(),
                $item->ubicacion
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
        $items   = $this->carrito->getCarritoByUser($codCliente);
        $empresa = currentCompany();

        // Porcentaje de IVA — multiempresa (ge_parametros codigo=17)
        $ivaConfig = DB::connection('odbc')->selectOne("
            SELECT TOP 1 parametro
            FROM DBA.GE_PARAMETROS
            WHERE empresa = ? AND codigo = 17
        ", [$empresa]);
        $porcentajeIva = (float) trim($ivaConfig->parametro ?? '0');

        // Modo de trabajo con IVA — multiempresa (web_ge_parametros codigo=248)
        // S = precio sin IVA (se suma encima)
        // N = precio con IVA incluido (se desglosa)
        $modoIvaConfig = DB::connection('odbc')->selectOne("
            SELECT TOP 1 parametro
            FROM DBA.web_ge_parametros
            WHERE empresa = ? AND codigo = 248
        ", [$empresa]);
        $trabajaConIvaIncluido = strtoupper(trim($modoIvaConfig->parametro ?? 'S'));

        $subtotal   = 0;
        $ivaTotal   = 0;
        $totalBruto = 0;

        foreach ($items as $item) {
            $precioLinea = (float) $item->pvp3 * (int) $item->cantidad;

            if (($item->iva ?? 'N') === 'S') {
                if ($trabajaConIvaIncluido === 'S') {
                    // Precio NO incluye IVA — se suma encima
                    $subtotal += $precioLinea;
                    $ivaTotal += $precioLinea * ($porcentajeIva / 100);
                } else {
                    // Precio YA incluye IVA — se desglosa
                    $subtotalLinea = $precioLinea / (1 + ($porcentajeIva / 100));
                    $subtotal      += $subtotalLinea;
                    $ivaTotal      += $precioLinea - $subtotalLinea;
                }
            } else {
                // Producto sin IVA
                $subtotal += $precioLinea;
            }

            $totalBruto += $precioLinea;
        }

        $total = $subtotal + $ivaTotal;

        return [
            'items'                 => $items,
            'subtotal'              => number_format($subtotal, 2, '.', ''),
            'iva'                   => number_format($ivaTotal, 2, '.', ''),
            'total'                 => number_format($total, 2, '.', ''),
            'totalBruto'            => number_format($totalBruto, 2, '.', ''),
            'porcentajeIva'         => $porcentajeIva,
            'trabajaConIvaIncluido' => $trabajaConIvaIncluido,
            'count'                 => count($items),
        ];
    }
}
