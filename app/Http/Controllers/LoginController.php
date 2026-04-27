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

    // REGISTRO — crea usuario nuevo en pw_ge_usuarios
    public function register(Request $request)
    {
        // Validar campos obligatorios
        if (!$request->email || !$request->password || !$request->nombre) {
            return response()->json([
                'error' => 'Faltan datos obligatorios: nombre, email y password'
            ], 400);
        }

        // Validar longitud tipo_identificacion
        if ($request->tipo_identificacion && strlen($request->tipo_identificacion) > 2) {
            return response()->json([
                'error' => 'tipo_identificacion máximo 2 caracteres'
            ], 400);
        }

        // Verificar si el email ya existe
        $existe = $this->model->findByEmail($request->email);
        if ($existe) {
            return response()->json([
                'error' => 'El email ya está registrado'
            ], 400);
        }

        // Hashear contraseña
        $passwordHash = Hash::make($request->password);

        // Insertar en BD
        $this->model->createUser([
            'pw_codigo'          => $request->pw_codigo,
            'nombre'             => $request->nombre,
            'cedula_ruc'         => $request->cedula_ruc,
            'email'              => $request->email,
            'contrasena'         => $passwordHash,
            'estado'             => $request->estado ?? 'A',
            'direccion'          => $request->direccion,
            'telefono'           => $request->telefono,
            'tipo_identificacion'=> $request->tipo_identificacion,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado correctamente'
        ]);
    }
}
