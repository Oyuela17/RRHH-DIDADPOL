<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SetPgAuditContext
{
    public function handle(Request $request, Closure $next)
    {
        // 1) IP real (con TrustProxies configurado)
        $ip = $request->ip() ?? '';

        // 2) Intentar por Auth
        $userId = Auth::id();

        // 3) Fallback: si Auth aún no está resuelto en esta ruta,
        //    leemos el user_id directamente de la tabla sessions.
        if (!$userId) {
            try {
                $sessionId = $request->session()->getId(); // id de la cookie "laravel_session"
                if ($sessionId) {
                    $row = DB::table('sessions')->where('id', $sessionId)->first();
                    if ($row && !empty($row->user_id)) {
                        $userId = (string) $row->user_id;
                    }
                }
            } catch (\Throwable $e) {
                // silencioso: no romper la request
            }
        }

        // 4) Enviar variables a PostgreSQL a NIVEL DE SESIÓN (false)
        //    para que alcancen todos los INSERT/UPDATE/DELETE del request
        DB::statement("select set_config('app.user_id', ?, false)", [$userId ? (string)$userId : '']);
        DB::statement("select set_config('app.ip', ?, false)", [$ip]);

        return $next($request);
    }
}
