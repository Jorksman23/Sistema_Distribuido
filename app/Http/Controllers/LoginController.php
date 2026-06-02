<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\login_model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Services\BrevoMailer;
use Illuminate\Support\Facades\URL;


class LoginController extends Controller
{
    protected $model;
     protected $mailer;

    public function __construct(BrevoMailer $mailer)
    {
        $this->model = new login_model();
         $this->mailer = $mailer;
    }

    // Mostrar formulario login
    public function showLogin()
    {
        return view('auth.login');
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
        if ($user->empresa !== config('app.company_code', '001')) {
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
            'user_id'  => $user->user_id,
            'nombre'   => $user->nombre,
            'email'    => $user->email,
            'pw_codigo'=> $user->pw_codigo,
            'cod_cliente'=> (string) $user->user_id,
        ]);
        return redirect('/');
    }

    // Procesar registro
    public function register(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($this->model->findByEmail($request->email)) {
            return back()->withErrors(['email' => 'El email ya está registrado.'])->withInput();
        }
        try{
            $this->model->createUser([
                'pw_codigo'           => 'USR' . substr(time(),-7),
                'nombre'              => $request->nombre,
                'cedula_ruc'          => $request->cedula_ruc,
                'email'               => $request->email,
                'contrasena'          => Hash::make($request->password),
                'estado'              => 'A',
                'direccion'           => $request->direccion,
                'telefono'            => $request->telefono,
                'tipo_identificacion' => $request->tipo_identificacion,
                'empresa'             => config('app.company_code', '001'),
                'email_verified_at'   => null,
        ]);
         $user = $this->model->findByEmail($request->email);

            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                ['id' => $user->user_id, 'hash' => sha1($user->email)]
            );
            $htmlContent = view('email.verify', compact('user', 'verificationUrl'))->render();

            $this->mailer->sendEmail(
            $user->email,
            'Verifica tu correo en ' . config('app.name'),
            $htmlContent
        );
        }catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al crear cuenta: ' . $e->getMessage()]);
        }
    }
    // Cerrar sesión
    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }
}
