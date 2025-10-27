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
        $ip = $request->ip() ?? '';
        $userId = Auth::id();

        if (!$userId) {
            try {
                $sessionId = $request->session()->getId();
                if ($sessionId) {
                    $row = DB::table('sessions')->where('id', $sessionId)->first();
                    if ($row && !empty($row->user_id)) {
                        $userId = (string) $row->user_id;
                    }
                }
            } catch (\Throwable $e) {
                // silencioso
            }
        }

        try {
            // set_config(scope: false) = scope sesión/conexión
            DB::statement("select set_config('app.user_id', ?, false)", [$userId ? (string)$userId : '']);
            DB::statement("select set_config('app.ip', ?, false)", [$ip]);

            $response = $next($request);
        } finally {
            // MUY IMPORTANTE: limpiar para que otra request no herede
            try {
                DB::statement("reset app.user_id");
                DB::statement("reset app.ip");
            } catch (\Throwable $e) {
                // ignorar
            }
        }

        return $response;
    }
}
