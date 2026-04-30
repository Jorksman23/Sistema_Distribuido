<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\login_model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = new Login_Model();
    }

    public function register(Request $request)
    {
        $request->validate([              // ✅ validación robusta
            'email'    => 'required|email',
            'password' => 'required|min:6',
            'nombre'   => 'required|string',
        ]);

        if ($this->model->findByEmail($request->email)) {
            return response()->json(['error' => 'El email ya está registrado'], 400);
        }

        $this->model->createUser([
            'pw_codigo'           => $request->pw_codigo ?? ('USR' . time()),
            'nombre'              => $request->nombre,
            'cedula_ruc'          => $request->cedula_ruc,
            'email'               => $request->email,
            'contrasena'          => Hash::make($request->password),
            'estado'              => 'A',
            'direccion'           => $request->direccion,
            'telefono'            => $request->telefono,
            'tipo_identificacion' => $request->tipo_identificacion,
        ]);

        $token = Str::random(60);
        $this->model->saveToken($request->email, $token); // ✅ método dedicado

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado correctamente',
            'token'   => $token
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = $this->model->findByEmail($request->email);

        if (!$user || !Hash::check($request->password, $user->contrasena)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        $token = Str::random(60);
        $this->model->saveToken($request->email, $token); // ✅

        return response()->json([
            'success' => true,
            'message' => 'Login correcto',
            'token'   => $token
        ]);
    }

    public function users()
    {
        return response()->json([
            'success' => true,
            'data'    => $this->model->getUsers()
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->attributes->get('user');

        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $request->validate(['nombre' => 'required|string']);

        $this->model->updateUser($user->user_id, [
            'nombre'    => $request->nombre,
            'direccion' => $request->direccion,
            'telefono'  => $request->telefono,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente'
        ]);
    }
}
