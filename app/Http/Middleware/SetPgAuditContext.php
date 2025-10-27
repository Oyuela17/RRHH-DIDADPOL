<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetPgAuditContext
{
    public function handle($request, Closure $next)
    {
        $userId = Auth::id();           // null si no hay login
        $ip     = $request->ip() ?? ''; // IP real (ver paso 2 para proxies)

        // IMPORTANTE: false => a nivel de sesión de la conexión, no solo transacción
        DB::statement("select set_config('app.user_id', ?, false)", [$userId ? (string)$userId : '']);
        DB::statement("select set_config('app.ip', ?, false)", [$ip]);

        return $next($request);
    }
}
