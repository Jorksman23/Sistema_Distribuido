<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $connection = 'odbc';
    protected $table = 'DBA.in_cliente';
    protected $primaryKey = 'codigo';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'direccion1',
        'telefono',
        'cedula_ruc',
        'empresa',
        'e_mail',
    ];

    public function usuario()
    {
        return $this->belongsTo(login_model::class, 'codigo', 'pw_codigo');
    }
}
