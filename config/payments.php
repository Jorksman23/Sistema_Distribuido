<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Secuencias de cxc_forma_pago por procesador
    |--------------------------------------------------------------------------
    |
    | Las secuencias son universales en las 5 empresas (001-005):
    |   - efectivo = secuencia 1
    |   - nuvei (tarjeta de crédito) = secuencia 5
    |
    | Solo se declaran las formas de pago con comportamiento ESPECIAL.
    | Cualquier otra secuencia (transferencias bancarias, cheques, etc.)
    | se trata como TRANSFERENCIA por defecto: el cliente sube comprobante
    | manual y el admin lo revisa.
    |
    */

    'efectivo' => 1,
    'nuvei'    => 5,

    /*
    |--------------------------------------------------------------------------
    | Credenciales y endpoints de Nuvei (Link to Pay) — SANDBOX
    |--------------------------------------------------------------------------
    |
    | Credenciales de DESARROLLO. Se leen del .env para no commitearlas.
    | En producción multiempresa, esto deberá convertirse en un mapa por
    | empresa (cada empresa = comercio fiscal distinto = credenciales
    | distintas), resuelto con currentCompany().
    |
    */

    'nuvei_config' => [
        'client_id'  => env('NUVEI_CLIENT_ID'),
        'server_key' => env('NUVEI_SERVER_KEY'),
        'init_url'   => env('NUVEI_INIT_URL', 'https://noccapi-stg.paymentez.com/linktopay/init_order/'),
        'expiration' => 36000,
    ],

];
