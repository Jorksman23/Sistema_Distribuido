<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InCliente;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    // Mostrar login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Procesar login — igual que check_login del modelo anterior
    public function login(Request $request)
{
    $request->validate([
        'usuario'    => 'required',
        'contrasena' => 'required',
    ]);

    $empresa = '001';

    $cliente = InCliente::where('empresa', $empresa)
        ->where(function($query) use ($request) {
            $query->where('e_mail', $request->usuario)
                  ->orWhere('contacto', $request->usuario);
        })
        ->where('contrasena', $request->contrasena)
        ->select('nombre', 'codigo', 'cedula_ruc', 'e_mail', 'contacto')
        ->first();

    if ($cliente) {
        return response()->json([
            'success' => true,
            'mensaje' => 'Login exitoso',
            'cliente' => $cliente
        ]);
    }

    return response()->json([
        'success' => false,
        'mensaje' => 'Credenciales incorrectas'
    ], 401);
}

    // Verificar si cedula_ruc ya existe — igual que check_for_cedula_ruc
    public function verificarCedula(Request $request, $tabla = 'in_cliente')
    {
        $empresa = '001';

        $existe = InCliente::where('empresa', $empresa)
            ->where('cedula_ruc', $request->cedula_ruc)
            ->select('cedula_ruc', 'nombre', 'contacto', 'e_mail', 'empresa')
            ->first();

        return $existe
            ? response()->json($existe)
            : response()->json(['error' => 'No encontrado'], 404);
    }

    // Obtener cliente nuevo por cedula + email — igual que get_new_client
    // Verificar que el cliente existe por cedula + email
public function getClienteNuevo(Request $request)
{
    $empresa = '001';

    $cliente = InCliente::where('empresa', $empresa)
        ->where('e_mail', $request->email)
        ->select('codigo', 'nombre', 'cedula_ruc', 'empresa', 'contacto', 'e_mail', 'contrasena')
        ->first();

    if ($cliente) {
        // Guardamos el codigo en la respuesta para usarlo en el siguiente paso
        return response()->json([
            'success' => true,
            'mensaje' => 'Cliente encontrado, procede a crear tu contraseña',
            'codigo'  => $cliente->codigo,
            'nombre'  => $cliente->nombre,
            'email'   => $cliente->e_mail,
        ]);
    }

    return response()->json([
        'success' => false,
        'mensaje' => 'No se encontró ningún cliente con ese email'
    ], 404);
}

// Crear contraseña
public function setPassword(Request $request)
{
    $request->validate([
        'codigo'     => 'required',
        'contrasena' => 'required|min:6',
    ]);

    $actualizado = InCliente::where('codigo', $request->codigo)
                            ->where('empresa', '001')
                            ->update(['contrasena' => $request->contrasena]);

    if ($actualizado) {
        return response()->json([
            'success' => true,
            'mensaje' => '¡Contraseña creada exitosamente! Ya puedes iniciar sesión.'
        ]);
    }

    return response()->json([
        'success' => false,
        'mensaje' => 'No se pudo actualizar la contraseña'
    ], 500);
}







    // Cerrar sesión
    public function logout()
    {
        Session::forget('cliente');
        return redirect()->route('login');
    }
}
