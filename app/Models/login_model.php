<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class login_model implements AuthenticatableContract
{
    use Authenticatable;

    protected $connection = 'odbc';

    public $user_id;
    public $pw_codigo;
    public $nombre;
    public $email;
    public $contrasena;
    public $estado;
    public $cedula_ruc;
    public $direccion;
    public $telefono;
    public $tipo_identificacion;
    public $empresa;
    public $email_verified_at;

    //Requeridos por Authenticatable
    public function getAuthIdentifierName(): string
    {
        return 'user_id';
    }

    public function getAuthIdentifier()
    {
        return $this->user_id;
    }

    public function getAuthPassword(): string
    {
        return (string) $this->contrasena;
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public static function mapRowToInstance($row): self
    {
        $instance = new self();
        $instance->user_id             = $row->user_id;
        $instance->pw_codigo           = $row->pw_codigo;
        $instance->nombre              = $row->nombre;
        $instance->email               = $row->email;
        $instance->contrasena          = $row->contrasena;
        $instance->estado              = $row->estado;
        $instance->empresa             = $row->empresa ?? null;
        $instance->cedula_ruc          = $row->cedula_ruc ?? null;
        $instance->direccion           = $row->direccion ?? null;
        $instance->telefono            = $row->telefono ?? null;
        $instance->tipo_identificacion = $row->tipo_identificacion ?? null;
        $instance->email_verified_at   = $row->email_verified_at ?? null;
        return $instance;
    }


}
