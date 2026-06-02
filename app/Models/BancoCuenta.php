<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BancoCuenta extends Model
{
    protected $table = 'te_cuentas_bancos';

    public $timestamps = false;
    public $cod_sistema;
    protected $fillable = [
        'cod_sistema',
        'empresa',
        'descripcion',
        'tipo',
        'moneda',
        'banco',
        'cuenta',
        'cta_contable',
        'formato_web',
        'view_on_comprobante'
    ];
}
