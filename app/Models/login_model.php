<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class login_model{
    protected $connection = 'odbc';
    protected $table = 'DBA.pw_ge_usuarios';
    public function findByEmail($email){
        return DB::connection($this->connection)
            ->selectOne("
                SELECT TOP 1 * FROM DBA.pw_ge_usuarios
                WHERE email = ?
            ", [$email]);
    }
    public function createUser($data){
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
            $data['estado']              ?? 'A',
            $data['direccion']           ?? null,
            $data['telefono']            ?? null,
            $data['tipo_identificacion'] ?? null,
        ]);
    }
    public function getUsers(){
        return DB::connection($this->connection)
            ->select("SELECT TOP 50 * FROM DBA.pw_ge_usuarios");
    }
}
