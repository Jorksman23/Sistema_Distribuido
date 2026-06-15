<?php

namespace App\Services;

use App\Models\CarritoModel;
use App\Repositories\CartRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

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
    public function obtenerDatosCliente(string $cedula)
    {
        if (!$cedula) {
            return ['error' => 'Cédula requerida'];
        }

        try {
            // Consultar servicio externo
            $url = "http://186.101.203.79:2001/persona/{$cedula}";
            $response = Http::timeout(5)->get($url);
            $data = $response->ok() ? $response->json() : [];

            // Consultar datos locales en SQL Anywhere (TOP en lugar de LIMIT)
            $clienteLocal = DB::select("
                SELECT TOP 1 * FROM in_cliente
                WHERE cedula_ruc = ? OR codigo = ?
            ", [$cedula, $cedula]);
            $clienteLocal = $clienteLocal ? (object)$clienteLocal[0] : null;

            $usuarioLocal = DB::select("
                SELECT TOP 1 * FROM pw_ge_usuarios
                WHERE cedula_ruc = ?
            ", [$cedula]);
            $usuarioLocal = $usuarioLocal ? (object)$usuarioLocal[0] : null;

            // Fusionar datos externos + locales
            $dataFinal = [
                'cedula_ruc' => $data['cedula_ruc'] ?? $clienteLocal?->cedula_ruc ?? $usuarioLocal?->cedula_ruc ?? '',
                'nombre'     => $data['nombre'] ?? $clienteLocal?->nombre ?? $usuarioLocal?->nombre ?? '',
                'direccion'  => $clienteLocal?->direccion1 ?? $usuarioLocal?->direccion ?? '',
                'telefono'   => $clienteLocal?->telefono ?? $usuarioLocal?->telefono ?? '',
                'email'      => $clienteLocal?->e_mail ?? $usuarioLocal?->email ?? '',
                'origen'     => $data['origen'] ?? 'LOCAL'
            ];

            return $dataFinal;
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

}
