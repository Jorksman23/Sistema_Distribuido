<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaPago extends Model
{
    protected $table = 'cxc_forma_pago';
    protected $primaryKey = 'secuencia';
    public $timestamps = false;
    public $cod_cuenta_banco;
    protected $fillable = [
        'forma_pago',
        'empresa',
        'tipo',
        'cuenta',
        'xml_forma_pago',
        'cod_cuenta_banco'
    ];
}
