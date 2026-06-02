<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class VerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $user = DB::connection('odbc')->selectOne(
            "SELECT TOP 1 * FROM DBA.pw_ge_usuarios WHERE user_id = ?", [$id]
        );

        if (!$user || sha1($user->email) !== $hash) {
            return redirect('/login')->withErrors(['email' => 'Enlace inválido o expirado.']);
        }

        if (!is_null($user->email_verified_at)) {
            return redirect('/login')->with('success', 'Tu correo ya estaba verificado.');
        }

        DB::connection('odbc')->update(
            "UPDATE DBA.pw_ge_usuarios SET email_verified_at = ? WHERE user_id = ?",
            [Carbon::now(), $id]
        );

        return redirect('/login')->with('success', 'Correo verificado correctamente. Ya puedes iniciar sesión.');
    }
}
