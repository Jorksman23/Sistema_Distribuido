<?php

namespace App\Http\Controllers;

use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use App\Repositories\LoginRepository;
use Illuminate\Support\Facades\Hash;
use App\Services\BrevoMailer;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;


class LoginController {

    protected $model;
     protected $mailer;

    public function __construct(LoginRepository $LoginRepository,BrevoMailer $mailer)
    {
        $this->model = $LoginRepository;
        $this->mailer = $mailer;
    }

    // Mostrar formulario login
    public function showLogin()
    {
        $ubicaciones = (new ProductRepository())->getUbicaciones(currentCompany());
        return view('auth.login', compact('ubicaciones'));
    }

    // Mostrar formulario register
    public function showRegister()
    {
        return view('auth.register');
    }

    // Procesar login
    public function login(Request $request){
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'ubicacion'=> 'required|string',
        ]);
        // Buscar usuario por email y empresa activa
        $user = $this->model->findByEmail($request->email);

        if (!$user || !Hash::check($request->password, $user->contrasena)) {
            return back()->withErrors([
                'email' => 'Credenciales incorrectas'
            ])->withInput();
        }
        // Verificar que el usuario esté activo
        if ($user->estado !== 'A') {
            return back()->withErrors([
                'email' => 'Tu cuenta está inactiva'
            ]);
        }
        // Verificar que el usuario pertenezca a la empresa activa
        if ($user->empresa !== config('app.company_code','?')) {
            return back()->withErrors([
                'email' => 'No tienes acceso a esta empresa.'
            ]);
        }
        if (is_null($user->email_verified_at)) {
        return back()->withErrors([
            'email' => 'Debes verificar tu correo antes de iniciar sesión.'
        ]);
        }
        // Guardar en sesión
        session([
            'user_id'              => $user->user_id,
            'nombre'                => $user->nombre,
            'email'                  => $user->email,
            'pw_codigo'             => $user->pw_codigo,
            'cod_cliente'           => (string) $user->user_id,
            'ubicacion_seleccionada' => $request->ubicacion,
        ]);
        return redirect('/');
    }
    // Procesar registro
    public function register(Request $request){
    $request->validate([
        'nombre'   => 'required|string',
        'email'    => 'required|email',
        'password' => 'required|min:6',
        ]);

    if ($this->model->findByEmail($request->email)) {
        return back()->withErrors(['email' => 'El email ya está registrado.'])->withInput();
        }

    try {
        $this->model->createUser([
            'pw_codigo'           => 'USR' . substr(time(), -7),
            'nombre'              => $request->nombre,
            'cedula_ruc'          => $request->cedula_ruc,
            'email'               => $request->email,
            'contrasena'          => Hash::make($request->password),
            'estado'              => 'A',
            'direccion'           => $request->direccion,
            'telefono'            => $request->telefono,
            'tipo_identificacion' => $request->tipo_identificacion,
            'empresa'             => config('app.company_code', '?'),
            'email_verified_at'   => null,
            ]);

        // Recuperar usuario recién creado
        $user = $this->model->findByEmail($request->email);

        // Generar URL de verificación firmada (válida por 60 minutos)
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->user_id, 'hash' => sha1($user->email)]
            );

        // Renderizar plantilla Blade del correo
        $htmlContent = view('email.verify', compact('user', 'verificationUrl'))->render();

        // Enviar correo con Brevo
        $this->mailer->sendEmail(
            $user->email,
            'Verifica tu correo en ' . config('app.name'),
            $htmlContent
            );

        // Guardar email en sesión para reenvío
        session(['email' => $request->email]);

        return redirect()->route('verification.notice')
            ->with('message', 'Cuenta creada. Revisa tu correo para verificarla.');
            } catch (\Exception $e) {
                return back()->withErrors(['error' => 'Error al crear cuenta: ' . $e->getMessage()]);
                }
    }

    public function resendVerification(Request $request)
{
    Log::info('Entró a resendVerification', ['email' => session('email')]);

    try {
        $email = session('email');
        if (!$email) {
            return back()->withErrors(['email' => 'No hay correo en sesión.']);
        }

        $user = $this->model->findByEmail($email);
        if (!$user) {
            return back()->withErrors(['email' => 'No se encontró el usuario.']);
        }

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->user_id, 'hash' => sha1($user->email)]
        );

        $htmlContent = view('email.verify', compact('user', 'verificationUrl'))->render();

        $result = $this->mailer->sendEmail(
            $user->email,
            'Verifica tu correo en ' . config('app.name'),
            $htmlContent
        );

        Log::info('Brevo resend result', $result);

        return back()->with('message', 'Se ha reenviado el enlace de verificación.');
    } catch (\Exception $e) {
        Log::error('Error en resendVerification: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Error al reenviar correo: ' . $e->getMessage()]);
    }
    }


    // Cerrar sesión
    public function logout()
    {
        $codCliente = (string) session('user_id');

        // Vaciar el carrito del usuario antes de cerrar sesión
        if ($codCliente) {
            try {
                (new \App\Repositories\CartRepository())->vaciar($codCliente);
            } catch (\Throwable $e) {
                // Si falla, continuamos con el logout de todas formas
            }
        }
        session()->flush();
        return redirect('/login');
    }
}
