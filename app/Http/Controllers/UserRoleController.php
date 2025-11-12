<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        // Guardar rol del usuario autenticado
        if (Auth::check()) {
            $rol = DB::table('roles')
                ->join('role_user', 'roles.id', '=', 'role_user.role_id')
                ->where('role_user.user_id', Auth::id())
                ->value('roles.nombre');

            session(['nombre_rol' => $rol ?? 'SIN ROL']);
        }

        // Filtros
        $busqueda = strtoupper((string) $request->input('buscar', ''));
        $cantidad = max((int) $request->input('registros', 5), 5);
        $orden = in_array($request->input('ordenar', 'nombre'), ['nombre', 'fecha']) ? $request->input('ordenar', 'nombre') : 'nombre';

        // ✅ Mostrar TODOS los usuarios (con o sin rol)
        $usuarios_roles = DB::table('users as u')
            ->leftJoin('role_user as ru', 'u.id', '=', 'ru.user_id')
            ->leftJoin('roles as r', 'ru.role_id', '=', 'r.id')
            ->select(
                'u.id',
                'u.name',
                'u.email',
                'u.estado',
                DB::raw("COALESCE(r.nombre, 'SIN ROL') as nombre_rol"),
                'r.id as role_id',
                'u.created_at'
            )
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                return $query->whereRaw("UPPER(u.name) LIKE ?", ["%{$busqueda}%"]);
            })
            ->when($orden === 'fecha', fn($q) => $q->orderBy('u.created_at', 'desc'))
            ->when($orden === 'nombre', fn($q) => $q->orderBy('u.name', 'asc'))
            ->paginate($cantidad)
            ->appends([
                'buscar' => $busqueda,
                'registros' => $cantidad,
                'ordenar' => $orden,
            ]);

        // Solo roles activos
        $roles = DB::table('roles')->where('estado', 'ACTIVO')->get();

        return view('usuarios_roles.index', compact('usuarios_roles', 'roles', 'busqueda', 'cantidad', 'orden'));
    }

    public function asignar(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'estado'  => 'required|in:ACTIVO,INACTIVO',
        ]);

        try {
            DB::beginTransaction();

            // Asignar o actualizar rol
            $existe = DB::table('role_user')->where('user_id', $id)->exists();

            if ($existe) {
                DB::table('role_user')
                    ->where('user_id', $id)
                    ->update(['role_id' => $request->role_id]);
            } else {
                DB::table('role_user')->insert([
                    'user_id'    => $id,
                    'role_id'    => $request->role_id,
                    'created_at' => now(),
                ]);
            }

            // Actualizar estado
            $estado = strtoupper($request->estado);
            DB::table('users')
                ->where('id', $id)
                ->update([
                    'estado' => $estado,
                    'updated_at' => now(),
                    'intentos_fallidos' => $estado === 'ACTIVO' ? 0 : DB::raw('intentos_fallidos'),
                ]);

            DB::commit();

            return redirect()
                ->route('usuarios_roles.index')
                ->with('success', 'Rol y estado actualizados correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->route('usuarios_roles.index')
                ->with('error', 'Error al asignar rol: ' . $e->getMessage());
        }
    }
}
