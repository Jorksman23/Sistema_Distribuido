<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\login_model;

class ProfileController extends Controller
{
    // ── Mostrar perfil ───────────────────────────────────
    public function show(Request $request)
    {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $model  = new login_model();
        $usuario = $model->findById($userId);

        if (!$usuario) {
            return redirect()->route('login');
        }

        return view('profile.show', [
            'usuario' => $usuario,
        ]);
    }

    // ── Actualizar datos personales ──────────────────────
    public function update(Request $request)
    {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'nombre'    => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
        ]);

        $model = new login_model();
        $model->updateUser($userId, [
            'nombre'    => $request->nombre,
            'email'     => $request->email,
            'telefono'  => $request->telefono,
            'direccion' => $request->direccion,
        ]);

        // Actualizar sesión con nuevo nombre
        session(['nombre' => $request->nombre]);

        return redirect()->route('profile.show')
                         ->with('success', 'Datos actualizados correctamente.');
    }

    // ── Cambiar contraseña ───────────────────────────────
    public function updatePassword(Request $request)
    {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'current'               => 'required',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        $model   = new login_model();
        $usuario = $model->findById($userId);

        // Verificar contraseña actual
        if (md5($request->current) !== $usuario->contrasena &&
            $request->current      !== $usuario->contrasena) {
            return back()->withErrors(['current' => 'La contraseña actual no es correcta.']);
        }

        // Guardar nueva contraseña
        $model->updatePassword($userId, md5($request->password));

        return redirect()->route('profile.show')
                         ->with('success_pass', '¡Contraseña cambiada correctamente!');
    }
}
