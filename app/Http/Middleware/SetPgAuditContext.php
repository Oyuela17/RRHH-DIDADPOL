<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetPgAuditContext
{
    public function handle($request, Closure $next)
    {
        // intenta varias formas de obtener el id
        $userId = Auth::id() ?? optional($request->user())->id ?? null;
        $ip     = $request->ip() ?? '';

        // nivel de SESIÓN (false) para que aplique a todas las queries del request
        DB::statement("select set_config('app.user_id', ?, false)", [$userId ? (string)$userId : '']);
        DB::statement("select set_config('app.ip', ?, false)", [$ip]);

        return $next($request);
    }
}
