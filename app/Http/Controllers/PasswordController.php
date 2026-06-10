<?php

namespace App\Http\Controllers;

use App\Services\PasswordResetService;
use Illuminate\Http\Request;


class PasswordController {

    public function __construct(protected PasswordResetService $resetService) {}

    /**
     * Muestra el formulario de solicitud de enlace de reseteo
     */
    public function requestForm()
    {
        return view('auth.Password_cambio');
    }


    /**
     * Envía el enlace de reseteo al correo
     */
    public function sendLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $ok = $this->resetService->sendResetLink($request->email);

        return $ok
    ? redirect()->route('password.sent')
    : back()->withErrors(['email' => 'No se pudo enviar el correo.']);

    }

    public function sent()
    {
        return view('auth.password_sent');
    }
    /**
     * Muestra el formulario para ingresar nueva contraseña
     */
   public function showResetForm(string $token, Request $request)
{
    return view('auth.reset_password', [
        'token' => $token,
        'email' => $request->email,
    ]);
}

    /**
     * Procesa el reseteo de contraseña
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $ok = $this->resetService->resetWithToken(
            $request->email,
            $request->token,
            $request->password
        );

        return $ok
            ? redirect()->route('login')->with('success', 'Contraseña actualizada.')
            : back()->withErrors(['token' => 'Token inválido o expirado.']);
    }
}
