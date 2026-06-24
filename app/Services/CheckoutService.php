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
            $items   = $this->carrito->getCarritoByUser($codCliente);
            $count   = $this->cartRepository->count($codCliente);
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

            $subtotal = 0;
            $ivaTotal = 0;

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
            }

            $total = $subtotal + $ivaTotal;

            return [
                'items'    => $items,
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'iva'      => number_format($ivaTotal, 2, '.', ''),
                'total'    => number_format($total, 2, '.', ''),
                'count'    => $count,
            ];

    }
}
