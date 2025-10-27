<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetPgAuditContext
{
    public function handle($request, Closure $next)
    {
        $userId = Auth::id();
        $ip     = $request->ip() ?? '';

        DB::statement("select set_config('app.user_id', ?, true)", [$userId ? (string)$userId : '']);
        DB::statement("select set_config('app.ip', ?, true)", [$ip]);

        return $next($request);
    }
}
