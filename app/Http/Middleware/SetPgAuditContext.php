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

        // app/Http/Middleware/SetPgAuditContext.php
              DB::statement("select set_config('app.user_id', ?, false)", [$userId ? (string)$userId : '']);
              DB::statement("select set_config('app.ip', ?, false)", [$ip]);


        return $next($request);
    }
}
