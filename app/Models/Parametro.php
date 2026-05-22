<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    protected $table = 'pw_parametros';

    protected $primaryKey = 'codigo';

    public $timestamps = false;

    protected $fillable = [
        'parametro',
        'descripcion',
        'detalle',
        'empresa'
    ];
}
