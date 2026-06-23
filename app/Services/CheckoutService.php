<?php

namespace App\Services;

use App\Models\CarritoModel;
use App\Repositories\CartRepository;
use Illuminate\Support\Facades\DB;

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
            $count = $this->cartRepository->count($codCliente);

            $subtotal = 0;
            $ivaTotal = 0;

            $ivaConfig = DB::connection('odbc')
                ->select("SELECT TOP 1 * FROM DBA.GE_PARAMETROS WHERE empresa = ? AND codigo = 17", [currentCompany()]);
            $porcentajeIva = (float)(isset($ivaConfig[0]) ? $ivaConfig[0]->parametro : 0);
         foreach ($items as $item) {

                $subtotalLinea =
                    (float) $item->pvp3 *
                    (int) $item->cantidad;

                $subtotal += $subtotalLinea;

                if (($item->iva ?? 'N') === 'S') {
                    $ivaTotal += ($subtotalLinea * $porcentajeIva) / 100;
                }
             }

        $total = $subtotal + $ivaTotal;

        return [
            'items'     => $items,
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'iva'      => number_format($ivaTotal, 2, '.', ''),
            'total'    => number_format($total, 2, '.', ''),
            'count'    => $count,
        ];
    }
}
