<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PaymentMethodService
{
    public function obtenerFormasPago(string $empresa)
    {
        return DB::connection('odbc')->select("
            SELECT *
            FROM cxc_forma_pago
            WHERE empresa = ?
            ORDER BY secuencia
        ", [$empresa]);
    }

    public function obtenerFormaPago(
        int $secuencia,
        string $empresa
    )
    {
        return DB::connection('odbc')->selectOne("
            SELECT TOP 1 *
            FROM cxc_forma_pago
            WHERE empresa = ?
            AND secuencia = ?
        ", [
            $empresa,
            $secuencia
        ]);
    }

    public function obtenerCuentaBanco($formaPago,string $empresa) {
        if (!$formaPago || !$formaPago->cod_cuenta_banco) {
            return null;
        }

        return DB::connection('odbc')->selectOne("
            SELECT TOP 1
                cod_sistema,
                descripcion,
                cuenta,
                tipo,
                cta_contable
            FROM te_cuentas_bancos
            WHERE empresa = ?
            AND cod_sistema = ?
        ", [
            $empresa,
            $formaPago->cod_cuenta_banco
        ]);
    }

    public function obtenerOrden(string $codigo, string $empresa){
        return DB::connection('odbc')->selectOne("
            SELECT *
            FROM PW_ORDENES_WEB
            WHERE codigo = ?
            AND empresa = ?
        ", [
            $codigo,
            $empresa
        ]);
    }
}
