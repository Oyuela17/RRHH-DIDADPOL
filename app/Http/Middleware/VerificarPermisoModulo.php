<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerificarPermisoModulo
{
  
    public function handle($request, Closure $next, $modulo)
    {
        // 1) Usuario autenticado
        if (!auth()->check()) {
            abort(403, 'No autenticado.');
        }

        // 2) Obtener TODOS los roles del usuario
        $rolesIds = auth()->user()->roles()->pluck('roles.id');
        if ($rolesIds->isEmpty()) {
            abort(403, 'No tienes roles asignados.');
        }

        // 3) Detectar si el parámetro parece slug y si existe la columna 'slug'
        $esSlugParam     = preg_match('/^[a-z0-9\-_]+$/', (string) $modulo) === 1;
        $tieneColSlug    = Schema::hasColumn('modulos', 'slug');

        // 4) Base de consulta (unión de roles + acceso)
        $query = DB::table('modulos')
            ->join('permisos', 'permisos.modulo_id', '=', 'modulos.id')
            ->whereIn('permisos.rol_id', $rolesIds)
            ->where('permisos.tiene_acceso', true);

        // 5) Filtro por slug (si aplica) o por nombre en mayúsculas, tolerante a espacios
        if ($esSlugParam && $tieneColSlug) {
            // Comparación por slug exacto
            $query->where('modulos.slug', $modulo);
        } else {
            // Comparación por nombre (UPPER y trim)
            $nombreNormalizado = trim(mb_strtoupper($modulo));
            $query->whereRaw('TRIM(UPPER(modulos.nombre)) = ?', [$nombreNormalizado]);
        }

        // 6) Existe al menos un permiso que permita acceso al módulo
        $tienePermiso = $query->exists();

        if (!$tienePermiso) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
