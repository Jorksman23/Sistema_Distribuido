<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ClientRepository
{
    protected string $conn = 'odbc';

    /**
     * Busca cliente por correo.
     */
    public function findByEmailOrContact(string $email): ?object
    {
        return DB::connection($this->conn)->selectOne(
            'SELECT TOP 1 * FROM in_cliente WHERE e_mail = ?',
            [$email]
        );
    }

    /**
     * Guarda el token de reseteo de contraseña.
     */
    public function storeResetToken(string $codigo, string $email, string $hash, \DateTimeInterface $expiresAt): void
    {
        $empresa = currentCompany();

        DB::connection($this->conn)->transaction(function () use ($empresa, $codigo, $email, $hash) {
            // Elimina tokens previos del mismo usuario
            DB::connection($this->conn)->delete(
                'DELETE FROM pw_password_resets WHERE empresa = ? AND email = ?',
                [$empresa, $email]
            );

            // Inserta nuevo token (sin expires_at)
            DB::connection($this->conn)->insert(
                'INSERT INTO pw_password_resets (empresa, codigo, email, token_hash, created_at)
                 VALUES (?, ?, ?, ?, ?)',
                [
                    $empresa,
                    $codigo,
                    $email,
                    $hash,
                    now()->toDateTimeString()
                ]
            );
        });
    }

    /**
     * Valida el token de reseteo.
     */
    public function validateResetToken(string $email, string $token): bool
    {
        $empresa = currentCompany();
        $hash = hash('sha256', $token);

        $row = DB::connection($this->conn)->selectOne(
            'SELECT TOP 1 token_hash, created_at FROM pw_password_resets
             WHERE empresa = ? AND email = ?',
            [$empresa, $email]
        );

        if (!$row) return false;
        if (!hash_equals($row->token_hash, $hash)) return false;

        // Expira en 1 hora desde created_at
        $expires = strtotime($row->created_at . ' +1 hour');
        return $expires > time();
    }

    /**
     * Actualiza la contraseña del cliente.
     */
    public function updatePassword(string $codigo, string $hashedPassword): void
{
    $empresa = currentCompany();

    // Actualiza en in_cliente
    DB::connection($this->conn)->update(
        'UPDATE in_cliente SET contrasena = ? WHERE empresa = ? AND codigo = ?',
        [$hashedPassword, $empresa, $codigo]
    );

    // Actualiza en ge_usuario
    DB::connection($this->conn)->update(
        'UPDATE pw_ge_usuarios SET contrasena = ? WHERE empresa = ? AND user_id = ?',
        [$hashedPassword, $empresa, $codigo]
    );
}

    /**
     * Elimina el token de reseteo.
     */
    public function deleteResetToken(string $email): void
    {
        $empresa = currentCompany();

        DB::connection($this->conn)->delete(
            'DELETE FROM pw_password_resets WHERE empresa = ? AND email = ?',
            [$empresa, $email]
        );
    }
}
