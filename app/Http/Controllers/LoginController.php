<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\login_model;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = new login_model();
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
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

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

        // Guardar en sesión
        session([
            'user_id'  => $user->user_id,
            'nombre'   => $user->nombre,
            'email'    => $user->email,
            'pw_codigo'=> $user->pw_codigo,
        ]);

        return redirect('/');
    }

    // Procesar register
    public function register(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($this->model->findByEmail($request->email)) {
            return back()->withErrors([
                'email' => 'El email ya está registrado'
            ])->withInput();
        }

        $this->model->createUser([
            'pw_codigo'           => 'USR' . time(),
            'nombre'              => $request->nombre,
            'cedula_ruc'          => $request->cedula_ruc,
            'email'               => $request->email,
            'contrasena'          => Hash::make($request->password),
            'estado'              => 'A',
            'direccion'           => $request->direccion,
            'telefono'            => $request->telefono,
            'tipo_identificacion' => $request->tipo_identificacion,
        ]);

        return redirect('/login')->with('success', 'Cuenta creada, puedes iniciar sesión');
    }

    // Cerrar sesión
    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }
}
