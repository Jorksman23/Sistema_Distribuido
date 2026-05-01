<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class login_model implements AuthenticatableContract
{
    use Authenticatable;

    protected $connection = 'odbc';

    // Datos del usuario cargados manualmente
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

    // ── Requeridos por Authenticatable ──────────────────
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

    // ── Cargar usuario desde BD ──────────────────────────
    public function findByEmail($email)
    {
        $row = DB::connection($this->connection)
            ->selectOne("SELECT TOP 1 * FROM DBA.pw_ge_usuarios WHERE email = ?", [$email]);

        if (!$row) return null;

        // Mapear columnas al objeto
        $instance = new self();
        $instance->user_id            = $row->user_id;
        $instance->pw_codigo          = $row->pw_codigo;
        $instance->nombre             = $row->nombre;
        $instance->email              = $row->email;
        $instance->contrasena         = $row->contrasena;
        $instance->estado             = $row->estado;
        $instance->cedula_ruc         = $row->cedula_ruc         ?? null;
        $instance->direccion          = $row->direccion          ?? null;
        $instance->telefono           = $row->telefono           ?? null;
        $instance->tipo_identificacion= $row->tipo_identificacion ?? null;

        return $instance;
    }

    public function createUser($data)
    {
        return DB::connection($this->connection)->insert("
            INSERT INTO DBA.pw_ge_usuarios
            (pw_codigo, nombre, cedula_ruc, email, contrasena, estado, direccion, telefono, tipo_identificacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $data['pw_codigo'],
            $data['nombre'],
            $data['cedula_ruc'],
            $data['email'],
            $data['contrasena'],
            $data['estado']               ?? 'A',
            $data['direccion']            ?? null,
            $data['telefono']             ?? null,
            $data['tipo_identificacion']  ?? null,
        ]);
    }

    public function getUsers()
    {
        return DB::connection($this->connection)
            ->select("SELECT * FROM DBA.pw_ge_usuarios");
    }

    public function updateUser($user_id, $data)
    {
        return DB::connection($this->connection)->update("
            UPDATE DBA.pw_ge_usuarios
            SET nombre = ?, direccion = ?, telefono = ?
            WHERE user_id = ?
        ", [
            $data['nombre'],
            $data['direccion'],
            $data['telefono'],
            $user_id
        ]);
    }
}
