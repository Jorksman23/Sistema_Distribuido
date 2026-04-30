<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Login_Model{
    private string $connection = 'odbc';
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
    public function saveToken($email, $token)  // ✅ Método dedicado para el token
    {
        return DB::connection($this->connection)->update("
            UPDATE DBA.pw_ge_usuarios SET api_token = ? WHERE email = ?
        ", [$token, $email]);
    }
    public function getUsers(){
        return DB::connection($this->connection)
            ->select("SELECT * FROM DBA.pw_ge_usuarios");
    }
    public function updateUser($user_id, $data){
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
