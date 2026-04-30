<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $connection = 'odbc';
    protected $table = 'DBA.pw_ge_usuarios';
    protected $primaryKey = 'user_id';

    public $timestamps = false; //

    protected $fillable = [
        'pw_codigo',
        'nombre',
        'cedula_ruc',
        'email',
        'contrasena',
        'estado',
        'direccion',
        'telefono',
        'tipo_identificacion'
    ];

    protected $hidden = [
        'contrasena'
    ];

    public function getAuthPassword()
    {
        return $this->contrasena;
    }
}
