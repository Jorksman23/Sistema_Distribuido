<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthToken
{
    public function handle(Request $request, Closure $next){
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'error' => 'Token requerido'
            ], 401);
        }

        $token = str_replace('Bearer ', '', $header);

        // validar en BD
        $user = DB::connection('odbc')->selectOne("
            SELECT TOP 1 user_id, nombre, email
            FROM DBA.pw_ge_usuarios
            WHERE api_token = ?
        ", [$token]);

        if (!$user) {
            return response()->json([
                'error' => 'Token inválido'
            ], 401);
        }
        // guardar usuario en request
        $request->attributes->set('user', $user);

        return $next($request);
    }
}
