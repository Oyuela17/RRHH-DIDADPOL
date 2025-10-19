<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class VerificarEstadoUsuario
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->estado !== 'ACTIVO') {
            $nombre = Auth::user()->name;

            Auth::logout();

            return redirect()
                ->route('login')
                ->with('error', "Hola $nombre, tu cuenta ha sido desactivada por el administrador.");
        }

        return $next($request);

        $rol = DB::table('roles')
    ->join('role_user', 'roles.id', '=', 'role_user.role_id')
    ->where('role_user.user_id', Auth::id())
    ->value('roles.nombre');

session(['nombre_rol' => $rol]);

    }
}
