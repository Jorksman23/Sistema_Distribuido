<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\login_model;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    protected $model;

    public function __construct()
    {
        $this->model = new login_model();
    }

    // Registrar_usuario
    public function register(Request $request)
{
    if (!$request->email || !$request->password || !$request->nombre) {
        return response()->json([
            'error' => 'Faltan datos obligatorios'
        ], 400);
    }

    $existe = $this->model->findByEmail($request->email);
    if ($existe) {
        return response()->json([
            'error' => 'El email ya está registrado'
        ], 400);
    }

    $passwordHash = Hash::make($request->password);

    // 1) Crear usuario
    $this->model->createUser([
        'pw_codigo' => $request->pw_codigo ?? ('USR' . time()),
        'nombre' => $request->nombre,
        'cedula_ruc' => $request->cedula_ruc,
        'email' => $request->email,
        'contrasena' => $passwordHash,
        'estado' => 'A',
        'direccion' => $request->direccion,
        'telefono' => $request->telefono,
        'tipo_identificacion'=> $request->tipo_identificacion,
    ]);

    // 2) Generar token
    $token = Str::random(60);

    // 3) Guardar token en BD
    DB::connection('odbc')->update("
        UPDATE DBA.pw_ge_usuarios
        SET api_token = ?
        WHERE email = ?
    ", [$token, $request->email]);

    return response()->json([
        'success' => true,
        'message' => 'Usuario registrado correctamente',
        'token' => $token
    ]);
}

    // Loguin
    public function login(Request $request){
        if (!$request->email || !$request->password) {
            return response()->json([
                'error' => 'Datos incompletos'
            ], 400);
        }
        $user = $this->model->findByEmail($request->email);
        if (!$user || !Hash::check($request->password, $user->contrasena)) {
            return response()->json([
                'error' => 'Credenciales incorrectas'
            ], 401);
        }
        // nuevo token
        $token = Str::random(60);

        DB::connection('odbc')->update("
            UPDATE DBA.pw_ge_usuarios
            SET api_token = ?
            WHERE email = ?
        ", [$token, $request->email]);

        return response()->json([
            'success' => true,
            'token' => $token
        ]);
    }

    //Obtener usuarios
    public function users(){
        $users = $this->model->getUsers();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }
}
