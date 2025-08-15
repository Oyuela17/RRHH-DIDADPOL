<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    // Mostrar vista con usuarios y roles
    public function index(Request $request)
    {
        // Cargar y guardar el rol del usuario autenticado en sesión
        if (Auth::check()) {
            $rol = DB::table('roles')
                ->join('role_user', 'roles.id', '=', 'role_user.role_id')
                ->where('role_user.user_id', Auth::id())
                ->value('roles.nombre');

            session(['nombre_rol' => $rol ?? 'SIN ROL']);
        }

        // Parámetros de búsqueda, cantidad y ordenamiento
        $busqueda = strtoupper($request->input('buscar'));
        $cantidad = $request->input('registros', 5);
        $orden = $request->input('ordenar', 'nombre');

        $usuarios_roles = DB::table('users')
            ->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
            ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.estado',
                'roles.nombre as nombre_rol',
                'roles.id as role_id',
                'users.created_at'
            )
            ->when($busqueda, function ($query, $busqueda) {
                return $query->whereRaw("UPPER(users.name) LIKE ?", ["%{$busqueda}%"]);
            })
            ->when($orden === 'fecha', function ($query) {
                return $query->orderBy('users.created_at', 'desc');
            }, function ($query) {
                return $query->orderBy('users.name', 'asc');
            })
            ->paginate($cantidad)
            ->appends([
                'buscar' => $busqueda,
                'registros' => $cantidad,
                'ordenar' => $orden,
            ]);

        $roles = DB::table('roles')->where('estado', 'ACTIVO')->get();

        return view('usuarios_roles.index', compact('usuarios_roles', 'roles', 'busqueda', 'cantidad', 'orden'));
    }

    // Asignar o actualizar rol y estado del usuario
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
                DB::table('role_user')
                    ->where('user_id', $id)
                    ->update([
                        'role_id' => $request->role_id,
                        'created_at' => now()
                    ]);
            } else {
                DB::table('role_user')->insert([
                    'user_id' => $id,
                    'role_id' => $request->role_id,
                    'created_at' => now()
                ]);
            }

            DB::table('users')
                ->where('id', $id)
                ->update([
                    'estado' => strtoupper($request->estado),
                    'updated_at' => now()
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
