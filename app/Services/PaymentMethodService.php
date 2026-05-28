<?php

namespace App\Services;

use App\Models\FormaPago;
use App\Models\BancoCuenta;


class PaymentMethodService
{
     public function obtenerFormasPago(string $empresa)
    {
        return FormaPago::where('empresa', $empresa)
            ->where('view_on_desktop', 'S')
            ->orderBy('secuencia')
            ->get();
    }

    public function obtenerFormaPago(int $secuencia, string $empresa): ?FormaPago
    {
        return FormaPago::where('empresa', $empresa)
            ->where('secuencia', $secuencia)
            ->first();
    }

    public function obtenerCuentaBanco($formaPago, string $empresa)
    {
        if (!$formaPago) {
            return null;
        }
        return BancoCuenta::where('empresa', $empresa)
            ->where('cta_contable', $formaPago->cuenta)
            ->first();
    }
}
