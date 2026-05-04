<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthCustom
{
    public function handle(Request $request, Closure $next)
    {
        // Si no existe sesión → redirige a login
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        // Si sí hay sesión → continúa
        return $next($request);
    }
}
