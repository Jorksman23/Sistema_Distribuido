<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class InCliente extends Authenticatable
{
    protected $table = 'in_cliente';
    public $timestamps = false;
    public $fillable = ['empresa',
                        'codigo',
                        'nombre',
                        'cedula_ruc',
                        'e_mail',
                        'contacto',
                        'contrasena'];
}
