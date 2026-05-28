<?php

namespace App\Services;

use App\Models\CarritoModel;
use App\Models\ProductsModel;
use App\Repositories\CartRepository;
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

    public function agregarProducto(array $data, string $codCliente): void{
        $presentacion = (int) ($data['presentacion'] ?? 0);
        if ($this->cartRepository->exists(
            $codCliente,
            $data['codigo_item'],
            $presentacion
        )) {
            $item = $this->carrito->getItemByProducto($codCliente,$data['codigo_item'],$presentacion);
            if ($item) {
                $this->cartRepository->updateCantidad($item->id_item_web,$codCliente,$item->cantidad + 1
                );
            }
            return;
        }
            $nombre = $data['nombre'] ?? null;
            $pvp3   = $data['pvp3'] ?? null;
            $imagen = $data['imagen'] ?? null;
        if (!$nombre || !$pvp3) {
            $producto = (new ProductsModel())->findByCodigo($data['codigo_item'],currentCompany());
            if (!$producto) {
                throw new \Exception('Producto no encontrado');
            }
            $nombre = $producto->descripcion1;
            $pvp3   = $producto->pvp1;
            $imagen = $producto->imagen;
        }
        $this->carrito->add([
            'codigo_item'  => $data['codigo_item'],
            'nombre'       => ProductsModel::cleanString($nombre),
            'costo_real'   => $pvp3,
            'pvp3'         => $pvp3,
            'cantidad'     => 1,
            'cod_cliente'  => $codCliente,
            'imagen'       => $imagen,
            'iva'          => 'N',
            'presentacion' => $presentacion,
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
        $total = $this->cartRepository->getTotal($codCliente);
        return [
            'items' => $items,
            'total' => number_format($total, 2, '.', ''),
            'count' => count($items),
        ];
    }
}
