<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class VerificarPermisoModulo
{
    public function handle($request, Closure $next, $modulo)
    {
        $modulosPermitidos = Session::get('modulosPermitidos', []);

        if (!in_array(strtoupper($modulo), $modulosPermitidos)) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
