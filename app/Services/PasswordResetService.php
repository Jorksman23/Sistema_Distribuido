<?php

namespace App\Services;

use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Repositories\ClientRepository;
use App\Services\BrevoMailer;

class PasswordResetService
{
    public function __construct(
        protected ClientRepository $clients,
        protected BrevoMailer $mailer
    ) {}

    /**
     * Envía el enlace de reseteo al correo indicado
     */
    public function sendResetLink(string $email): bool
    {
        // Buscar cliente por correo
        $cliente = $this->clients->findByEmailOrContact($email);
        if (!$cliente) {
            return false;
        }

        // Generar token y hash
        $token = Str::random(60);
        $hash  = hash('sha256', $token);

        // Guardar token en pw_password_resets
        $this->clients->storeResetToken(
            $cliente->codigo,
            $email,
            $hash,
            Carbon::now()->addHour()
        );

        // Construir enlace de reseteo
        $url = route('password.reset.form', [
            'token' => $token,
            'email' => $email,
        ]);

        // Renderizar la vista Blade como HTML
        $html = view('email.password_reset', [
            'cliente' => $cliente,
            'url'     => $url,
        ])->render();

        // Enviar correo con BrevoMailer (API HTTP)
        $result = $this->mailer->sendEmail(
            $email,
            'Recupera tu contraseña',
            $html
        );

        return isset($result['messageId']);
    }

    /**
     * Procesa el reseteo de contraseña
     */
    public function resetWithToken(string $email, string $token, string $password): bool
    {
        // Validar token
        if (!$this->clients->validateResetToken($email, $token)) {
            return false;
        }

        // Buscar cliente para obtener su código
        $cliente = $this->clients->findByEmailOrContact($email);
        if (!$cliente) {
            return false;
        }

        // Actualizar contraseña en in_cliente y ge_usuario
        $this->clients->updatePassword($cliente->codigo, bcrypt($password));

        // Eliminar token usado
        $this->clients->deleteResetToken($email);

        return true;
    }
}
