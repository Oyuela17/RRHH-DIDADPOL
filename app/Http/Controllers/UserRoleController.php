<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRoleController extends Controller
{
    /**
     * Mostrar vista con usuarios y roles
     * Plan A: API /api/usuarios
     * Plan B: BD (fallback)
     */
    public function index(Request $request)
    {
        // Rol del usuario autenticado (insignia)
        if (Auth::check()) {
            $rol = DB::table('roles')
                ->join('role_user', 'roles.id', '=', 'role_user.role_id')
                ->where('role_user.user_id', Auth::id())
                ->value('roles.nombre');
            session(['nombre_rol' => $rol ?? 'SIN ROL']);
        }

        // Parámetros UI
        $busqueda = strtoupper((string) $request->input('buscar', ''));
        $orden    = $request->input('ordenar', 'nombre'); // nombre|fecha
        if (!in_array($orden, ['nombre', 'fecha'], true)) $orden = 'nombre';
        $perPage  = max((int) $request->input('registros', 10), 5);
        $page     = max((int) $request->input('page', 1), 1);

        // ---- PLAN A: API ----
        try {
            $url  = 'https://rrhh-didadpol-1.onrender.com/api/usuarios';

            // Si hay problemas de SSL en el host, descomenta ->withoutVerifying()
            $resp = Http::timeout(10)
                //->withoutVerifying()
                ->acceptJson()
                ->get($url)
                ->throw(); // lanza excepción si HTTP != 2xx

            $all = collect($resp->json()); // id, name, email, estado, nombre_rol, role_id

            // Filtro por nombre
            if ($busqueda !== '') {
                $all = $all->filter(fn ($u) =>
                    str_contains(strtoupper($u['name'] ?? ''), $busqueda)
                )->values();
            }

            // Orden (no viene created_at; usamos id como proxy de “fecha”)
            if ($orden === 'fecha') {
                $all = $all->sortByDesc('id')->values();
            } else {
                $all = $all->sortBy(fn ($u) => strtoupper($u['name'] ?? ''))->values();
            }

            // Paginación manual
            $total = $all->count();
            $items = $all->forPage($page, $perPage)->values();

            $usuarios_roles = new LengthAwarePaginator(
                $items, $total, $perPage, $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // Roles activos para el modal (desde BD)
            $roles = DB::table('roles')->where('estado', 'ACTIVO')->get();

            return view('usuarios_roles.index', [
                'usuarios_roles' => $usuarios_roles,
                'roles'          => $roles,
                'busqueda'       => $busqueda,
                'cantidad'       => $perPage,
                'orden'          => $orden,
            ]);

        } catch (\Throwable $e) {
            // Logueamos por qué falló el Plan A y caemos al Plan B
            Log::error('Fallo API /api/usuarios: '.$e->getMessage());
        }

        // ---- PLAN B (FALLBACK): BD con subconsultas, sin JOIN que esconda usuarios) ----
        $base = DB::table('users as u')
            ->select([
                'u.id',
                'u.name',
                'u.email',
                'u.estado',
                'u.created_at',
                DB::raw("(SELECT ru.role_id FROM role_user ru WHERE ru.user_id = u.id LIMIT 1) as role_id"),
                DB::raw("(SELECT r.nombre 
                          FROM role_user ru 
                          JOIN roles r ON r.id = ru.role_id 
                          WHERE ru.user_id = u.id LIMIT 1) as nombre_rol"),
            ]);

        if ($busqueda !== '') {
            $base->whereRaw("UPPER(u.name) LIKE ?", ["%{$busqueda}%"]);
        }

        if ($orden === 'fecha') {
            $base->orderBy('u.created_at', 'desc');
        } else {
            $base->orderBy('u.name', 'asc');
        }

        $total = (clone $base)->count(); // cuenta sobre users
        $items = $base->forPage($page, $perPage)->get();

        $usuarios_roles = new LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $roles = DB::table('roles')->where('estado', 'ACTIVO')->get();

        return view('usuarios_roles.index', [
            'usuarios_roles' => $usuarios_roles,
            'roles'          => $roles,
            'busqueda'       => $busqueda,
            'cantidad'       => $perPage,
            'orden'          => $orden,
        ]);
    }

    /**
     * Asignar o actualizar rol y estado del usuario (contra BD)
     */
    public function asignar(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'estado'  => 'required|in:ACTIVO,INACTIVO',
        ]);

        try {
            DB::beginTransaction();

            $existe = DB::table('role_user')->where('user_id', $id)->exists();

            if ($existe) {
                // role_user puede no tener updated_at → no lo toques
                DB::table('role_user')
                    ->where('user_id', $id)
                    ->update([
                        'role_id' => $request->role_id,
                    ]);
            } else {
                DB::table('role_user')->insert([
                    'user_id'    => $id,
                    'role_id'    => $request->role_id,
                    'created_at' => now(),
                ]);
            }

            DB::table('users')->where('id', $id)->update([
                'estado'     => strtoupper($request->estado),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('usuarios_roles.index')
                ->with('success', '✅ Rol y estado actualizados correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->route('usuarios_roles.index')
                ->with('error', '❌ Error al asignar rol: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un usuario (y su relación de rol)
     */
    public function eliminar($id)
    {
        try {
            DB::beginTransaction();

            DB::table('role_user')->where('user_id', $id)->delete();
            $eliminado = DB::table('users')->where('id', $id)->delete();

            if ($eliminado === 0) {
                DB::rollBack();
                return redirect()
                    ->route('usuarios_roles.index')
                    ->with('error', '⚠️ Usuario no encontrado.');
            }

            DB::commit();

            return redirect()
                ->route('usuarios_roles.index')
                ->with('success', '🗑️ Usuario eliminado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->route('usuarios_roles.index')
                ->with('error', '❌ Error al eliminar usuario: ' . $e->getMessage());
        }
    }
}
