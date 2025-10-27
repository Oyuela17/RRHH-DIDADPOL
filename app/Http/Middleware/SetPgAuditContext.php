<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetPgAuditContext
{
    public function handle($request, Closure $next)
    {
        // 1) Datos
        $userId = Auth::id();                 // null si no hay login
        $ip     = $request->ip() ?? '';

        // 2) Enviar variables de sesión a PostgreSQL (conexion por defecto)
        //    Usa true para que aplique a la transacción/conn actual
        DB::statement("select set_config('app.user_id', ?, true)", [$userId ? (string)$userId : '']);
        DB::statement("select set_config('app.ip', ?, true)", [$ip]);

        // (Opcional) si usas más conexiones pgsql, repite con DB::connection('pgsql2')->statement(...)

        return $next($request);
    }
}
