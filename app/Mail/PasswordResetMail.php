<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $email;
    public string $token;
public object $cliente;

public function __construct(string $email, string $token, object $cliente)
{
    $this->email = $email;
    $this->token = $token;
    $this->cliente = $cliente;
}

public function build()
{
    return $this->subject('Recupera tu contraseña')
                ->view('email.password_reset')
                ->with([
                    'url' => route('password.reset.form', [
                        'token' => $this->token,
                        'email' => $this->email,
                    ]),
                    'cliente' => $this->cliente,
                ]);
}
}
