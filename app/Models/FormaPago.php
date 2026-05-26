<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaPago extends Model
{
    protected $table = 'cxc_forma_pago';
    protected $primaryKey = 'secuencia';
    public $timestamps = false;
    protected $fillable = ['secuencia', 'forma_pago', 'empresa'];
}
