<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;                 // 👈 usamos la API
use Illuminate\Pagination\LengthAwarePaginator;     // 👈 paginación manual

class UserRoleController extends Controller
{
    /**
     * Mostrar vista con usuarios y roles (lista desde la API)
     */
    public function index(Request $request)
    {
        // Rol del usuario autenticado (insignia del header)
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

        // 🔗 URL directa a tu API
        $url = 'https://rrhh-didadpol-1.onrender.com/api/usuarios';

        // Llamar API
        $resp = Http::timeout(10)->get($url);
        if ($resp->failed()) {
            return back()->with('error', 'No se pudo obtener la lista desde la API.');
        }

        // Colección completa tal cual devuelve la API
        // Campos esperados: id, name, email, estado, nombre_rol, role_id
        $all = collect($resp->json());

        // Filtro por nombre
        if ($busqueda !== '') {
            $all = $all->filter(fn ($u) => str_contains(strtoupper($u['name'] ?? ''), $busqueda))->values();
        }

        // Orden (la API no trae created_at; usamos id como proxy de “fecha”)
        if ($orden === 'fecha') {
            $all = $all->sortByDesc('id')->values();
        } else {
            $all = $all->sortBy(fn ($u) => strtoupper($u['name'] ?? ''))->values();
        }

        // Paginación manual sobre la colección
        $total = $all->count();
        $items = $all->forPage($page, $perPage)->values();

        $usuarios_roles = new LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Roles activos para el modal (desde BD; si quieres, luego los pasamos a la API)
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
     * Asignar o actualizar rol y estado del usuario (contra BD por ahora)
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
                // ⚠️ Sin updated_at para evitar error si no existe esa columna
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
                // si tienes trigger que resetea intentos_fallidos al activar, puedes omitirlo
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
     * Eliminar un usuario (y su relación de rol) — contra BD
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
