<?php

namespace App\Services;

use App\Models\CarritoModel;
use App\Repositories\CartRepository;
class CheckoutService
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

    public function obtenerCheckout(string $codCliente): array{
        $items = $this->carrito->getCarritoByUser($codCliente);
        $total = $this->cartRepository->getTotal($codCliente);
        return [
            'items' => $items,
            'total' => number_format($total, 2, '.', ''),
            'count' => count($items),
        ];
    }
}
