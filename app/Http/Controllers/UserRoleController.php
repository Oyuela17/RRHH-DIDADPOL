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
        // Guardar en sesión el rol del usuario autenticado (si existe)
        if (Auth::check()) {
            $rolSesion = DB::table('roles')
                ->join('role_user', 'roles.id', '=', 'role_user.role_id')
                ->where('role_user.user_id', Auth::id())
                ->value('roles.nombre');

            session(['nombre_rol' => $rolSesion ?? 'SIN ROL']);
        }

        // Parámetros de búsqueda / paginación / orden
        $busqueda = strtoupper(trim((string) $request->input('buscar', '')));
        $cantidad = (int) $request->input('registros', 5);
        if ($cantidad <= 0) { $cantidad = 5; }

        $orden = $request->input('ordenar', 'nombre');
        if (!in_array($orden, ['nombre', 'fecha'], true)) {
            $orden = 'nombre';
        }

        // Consulta SIN duplicados por usuario, mostrando "SIN ROL" cuando no tenga
        $usuarios_roles = DB::table('users as u')
            ->leftJoin('role_user as ru', 'ru.user_id', '=', 'u.id')
            ->leftJoin('roles as r', 'r.id', '=', 'ru.role_id')
            ->select(
                'u.id',
                'u.name',
                'u.email',
                'u.estado',
                'u.created_at',
                // Si un usuario tiene varios roles, tomamos uno (por ejemplo el máx alfabético)
                DB::raw("COALESCE(MAX(r.nombre), 'SIN ROL') AS nombre_rol"),
                DB::raw("MAX(r.id) AS role_id")
            )
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                // Busca por nombre o correo (case-insensitive)
                return $q->where(function ($w) use ($busqueda) {
                    $w->whereRaw('UPPER(u.name) LIKE ?', ["%{$busqueda}%"])
                      ->orWhereRaw('UPPER(u.email) LIKE ?', ["%{$busqueda}%"]);
                });
            })
            // Agrupamos por campos de users para colapsar múltiples roles a 1 fila por usuario
            ->groupBy('u.id', 'u.name', 'u.email', 'u.estado', 'u.created_at')
            // Orden
            ->when($orden === 'fecha', function ($q) {
                return $q->orderBy('u.created_at', 'desc');
            }, function ($q) {
                return $q->orderBy('u.name', 'asc');
            })
            ->paginate($cantidad)
            ->appends([
                'buscar'    => $request->input('buscar', ''),
                'registros' => $cantidad,
                'ordenar'   => $orden,
            ]);

        // Solo roles ACTIVO para el selector
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

            // Aseguramos que el usuario tenga SOLO UNA fila en role_user (opcional pero sano)
            // Si tuviera varias, las dejamos en una sola con el role_id solicitado.
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
                    'created_at' => now(), // si la columna no existe, no falla
                ]);
            }

            // Actualizar estado del usuario
            $estado = strtoupper($request->estado);
            $datosUsuario = [
                'estado'     => $estado,
                'updated_at' => now(),
            ];
            if ($estado === 'ACTIVO') {
                // Resetear intentos fallidos al reactivar
                $datosUsuario['intentos_fallidos'] = 0;
            }

            DB::table('users')->where('id', $id)->update($datosUsuario);

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
