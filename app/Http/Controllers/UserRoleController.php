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
        $perPage  = max((int) $request->input('registros', 5), 5);   // ✅ por defecto 5
        $page     = max((int) $request->input('page', 1), 1);

        $apiBase = rtrim(env('API_BASE_URL', 'https://rrhh-didadpol-1.onrender.com'), '/');

        // ---- PLAN A: API ----
        try {
            $resp = Http::timeout(10)
                // ->withoutVerifying()
                ->acceptJson()
                ->get($apiBase . '/api/usuarios')
                ->throw(); // lanza excepción si HTTP != 2xx

            $all = collect($resp->json()); // id, name, email, estado, nombre_rol, role_id

            // Filtro por nombre
            if ($busqueda !== '') {
                $all = $all->filter(fn ($u) =>
                    str_contains(strtoupper($u['name'] ?? ''), $busqueda)
                )->values();
            }

            // Orden (la API no trae created_at; usamos id como proxy de “fecha”)
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
            Log::error('Fallo API /api/usuarios: '.$e->getMessage());
        }

        // ---- PLAN B (FALLBACK): BD con subconsultas que no esconden usuarios) ----
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
     * Asignar o actualizar rol y estado del usuario (vía API, con fallback a BD)
     */
    public function asignar(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'estado'  => 'required|in:ACTIVO,INACTIVO',
        ]);

        $apiBase = rtrim(env('API_BASE_URL', 'https://rrhh-didadpol-1.onrender.com'), '/');

        // ---- PLAN A: API ----
        try {
            // 1) Asignar rol: primero intento POST (asignación inicial)
            $post = Http::timeout(10)
                // ->withoutVerifying()
                ->acceptJson()
                ->post($apiBase . "/api/usuarios/{$id}/rol", [
                    'role_id' => (int) $request->role_id,
                ]);

            if ($post->status() === 409) {
                // Ya tiene rol → actualizo con PUT
                Http::timeout(10)
                    // ->withoutVerifying()
                    ->acceptJson()
                    ->put($apiBase . "/api/usuarios/{$id}/rol", [
                        'role_id' => (int) $request->role_id,
                    ])
                    ->throw();
            } elseif ($post->failed()) {
                $post->throw(); // lanza si fue otro error
            }

            // 2) Actualizar estado
            Http::timeout(10)
                // ->withoutVerifying()
                ->acceptJson()
                ->put($apiBase . "/api/usuarios/{$id}/estado", [
                    'estado' => strtoupper($request->estado),
                ])
                ->throw();

            return redirect()
                ->route('usuarios_roles.index')
                ->with('success', 'La operación se realizó correctamente: el rol y el estado del usuario han sido actualizados.');
        } catch (\Throwable $e) {
            Log::error('Fallo API asignar rol/estado: '.$e->getMessage());
            // Continúo al fallback
        }

        // ---- PLAN B (FALLBACK): BD ----
        try {
            DB::beginTransaction();

            $existe = DB::table('role_user')->where('user_id', $id)->exists();

            if ($existe) {
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
                ->with('success', 'La operación se realizó correctamente (modo contingencia): rol y estado actualizados desde la base de datos.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->route('usuarios_roles.index')
                ->with('error', 'No fue posible completar la operación. Detalle técnico: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un usuario (vía API, con fallback a BD)
     */
    public function eliminar($id)
    {
        $apiBase = rtrim(env('API_BASE_URL', 'https://rrhh-didadpol-1.onrender.com'), '/');

        // ---- PLAN A: API ----
        try {
            Http::timeout(10)
                // ->withoutVerifying()
                ->acceptJson()
                ->delete($apiBase . "/api/usuarios/{$id}")
                ->throw();

            return redirect()
                ->route('usuarios_roles.index')
                ->with('success', 'El usuario ha sido eliminado correctamente.');
        } catch (\Throwable $e) {
            Log::error('Fallo API eliminar usuario: '.$e->getMessage());
            // Continúo al fallback
        }

        // ---- PLAN B (FALLBACK): BD ----
        try {
            DB::beginTransaction();

            DB::table('role_user')->where('user_id', $id)->delete();
            $eliminado = DB::table('users')->where('id', $id)->delete();

            if ($eliminado === 0) {
                DB::rollBack();
                return redirect()
                    ->route('usuarios_roles.index')
                    ->with('error', 'No se encontró el usuario que intentaba eliminar.');
            }

            DB::commit();

            return redirect()
                ->route('usuarios_roles.index')
                ->with('success', 'El usuario ha sido eliminado correctamente (modo contingencia).');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->route('usuarios_roles.index')
                ->with('error', 'No fue posible eliminar el usuario. Detalle técnico: ' . $e->getMessage());
        }
    }
}
