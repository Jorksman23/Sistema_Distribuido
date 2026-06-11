<?php

namespace App\Repositories;
use App\Models\login_model;
use Illuminate\Support\Facades\DB;


class LoginRepository
{
    protected $connection = 'odbc';

    public function findByEmail(string $email): ?login_model{
        $empresa = currentCompany();
        $row = DB::connection($this->connection)
            ->selectOne("SELECT TOP 1 * FROM DBA.pw_ge_usuarios WHERE email = ? AND empresa = ?", [$email, $empresa]);

        return $row ? login_model::mapRowToInstance($row) : null;
    }
    public function createUser($data){
        return DB::connection($this->connection)->insert("
            INSERT INTO DBA.pw_ge_usuarios
            (pw_codigo, nombre, cedula_ruc, email, contrasena, estado, direccion, telefono, tipo_identificacion,empresa)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $data['empresa'],
            null,
        ]);
    }

    public function getUsers()
    {
        return DB::connection($this->connection)->select("SELECT * FROM DBA.pw_ge_usuarios");
    }

    public function updateUser($user_id, $data){
        return DB::connection($this->connection)->update("
            UPDATE DBA.pw_ge_usuarios
            SET nombre    = ?,
                direccion = ?,
                telefono  = ?
            WHERE user_id = ?
        ", [
            $data['nombre'],
            $data['direccion'],
            $data['telefono'],
            $user_id
        ]);
    }

    // ── Buscar usuario por ID ────────────────────────────
    public function findById(int $userId): ?login_model
    {
        $row = DB::connection($this->connection)
            ->selectOne("SELECT TOP 1 * FROM DBA.pw_ge_usuarios WHERE user_id = ?", [$userId]);

        return $row ? login_model::mapRowToInstance($row) : null;
    }
     //Actualizar contraseña
    public function updatePassword($userId, string $nuevaContrasena){
            return DB::connection($this->connection)->update("
                UPDATE DBA.pw_ge_usuarios
                SET contrasena = ?
                WHERE user_id = ?
            ", [$nuevaContrasena, $userId]);
    }

}
