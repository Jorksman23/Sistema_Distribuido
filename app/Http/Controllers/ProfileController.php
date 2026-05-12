<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\login_model;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller{
    protected $model;
    public function __construct()
    {
        $this->model = new login_model();
    }
    // ── Mostrar perfil ───────────────────────────────────
    public function show(Request $request){
        $userId = session('user_id');
        if (!$userId)return redirect()->route('login');

        $usuario = $this->model->findById($userId);
        if (!$usuario)return redirect()->route('login');

        return view('profile.show', ['usuario' => $usuario,]);
    }

    // ── Actualizar datos personales ──────────────────────
    public function update(Request $request)
    {
        $userId = session('user_id');
        if (!$userId)return redirect()->route('login');
        $request->validate([
            'nombre'    => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
        ]);
        $this->model->updateUser($userId, [
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
    public function updatePassword(Request $request){
        $userId = session('user_id');
        if (!$userId) return redirect()->route('login');
        $request->validate([
            'current'               => 'required',
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);
        $usuario = $this->model->findById($userId);
        // ── Verificar contraseña actual con bcrypt ──────────
        if (!Hash::check($request->current, $usuario->contrasena)) {
            return back()->withErrors(['current' => 'La contraseña actual no es correcta.']);
        }
        // ── Guardar nueva contraseña con bcrypt ─────────────
        $this->model->updatePassword($userId, Hash::make($request->password));
        return redirect()->route('profile.show')
                         ->with('success_pass', '¡Contraseña cambiada correctamente!');
    }
}
