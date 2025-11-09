<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class VerificarPermisoModulo
{
    /**
     * Uso en rutas:
     *   ->middleware('permiso:backups')             // por slug
     *   ->middleware('permiso:REPORTES')           // por nombre exacto
     */
    public function handle($request, Closure $next, $modulo)
    {
        if (!auth()->check()) {
            abort(403, 'No autenticado.');
        }

        $rolesIds = auth()->user()->roles()->pluck('roles.id');

        // Detecta si el parámetro parece un slug (minúsculas / guiones / underscores)
        $esSlug = preg_match('/^[a-z0-9\-_]+$/', $modulo) === 1;

        $query = DB::table('modulos')
            ->join('permisos', 'permisos.modulo_id', '=', 'modulos.id')
            ->whereIn('permisos.rol_id', $rolesIds)
            ->where('permisos.tiene_acceso', true);

        $query = $esSlug
            ? $query->where('modulos.slug', $modulo)                      // slug
            : $query->whereRaw('UPPER(modulos.nombre) = ?', [mb_strtoupper($modulo)]); // nombre

        $tiene = $query->exists();

        if (!$tiene) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
