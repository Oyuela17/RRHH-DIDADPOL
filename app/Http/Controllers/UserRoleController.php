<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    /**
     * Mostrar vista con usuarios y roles
     */
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
        $busqueda = strtoupper((string) $request->input('buscar', ''));
        $cantidad = max((int) $request->input('registros', 5), 5);

        $orden = $request->input('ordenar', 'nombre');
        if (!in_array($orden, ['nombre', 'fecha'], true)) {
            $orden = 'nombre';
        }

        // ✅ Consulta robusta con alias y COALESCE (garantiza mostrar todos los usuarios)
        $usuarios_roles = DB::table('users as u')
            ->select(
                'u.id',
                'u.name',
                'u.email',
                'u.estado',
                DB::raw("COALESCE(r.nombre, 'SIN ROL') as nombre_rol"),
                DB::raw("r.id as role_id"),
                'u.created_at'
            )
            ->leftJoin('role_user as ru', 'ru.user_id', '=', 'u.id')
            ->leftJoin('roles as r', 'r.id', '=', 'ru.role_id')
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                return $query->whereRaw("UPPER(u.name) LIKE ?", ["%{$busqueda}%"]);
            })
            ->when($orden === 'fecha', function ($query) {
                return $query->orderBy('u.created_at', 'desc');
            }, function ($query) {
                return $query->orderBy('u.name', 'asc');
            })
            ->paginate($cantidad)
            ->appends([
                'buscar'   => $request->input('buscar', ''),
                'registros'=> $cantidad,
                'ordenar'  => $orden,
            ]);

        // Solo roles activos para asignar
        $roles = DB::table('roles')->where('estado', 'ACTIVO')->get();

        return view('usuarios_roles.index', compact('usuarios_roles', 'roles', 'busqueda', 'cantidad', 'orden'));
    }

    /**
     * Asignar o actualizar rol y estado del usuario
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
                DB::table('role_user')
                    ->where('user_id', $id)
                    ->update([
                        'role_id'    => $request->role_id,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('role_user')->insert([
                    'user_id'    => $id,
                    'role_id'    => $request->role_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Actualizar estado de usuario
            $estado = strtoupper($request->estado);
            $datosUsuario = [
                'estado'     => $estado,
                'updated_at' => now(),
            ];

            if ($estado === 'ACTIVO') {
                $datosUsuario['intentos_fallidos'] = 0;
            }

            DB::table('users')->where('id', $id)->update($datosUsuario);

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

            // Borrar primero relación en role_user
            DB::table('role_user')->where('user_id', $id)->delete();

            // Luego borrar el usuario
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
