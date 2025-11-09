<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermisosController extends Controller
{
    /**
     * Mostrar listado de roles con búsqueda, orden y paginación para asignar permisos.
     */
    public function index(Request $request)
    {
        $busqueda = strtoupper($request->input('busqueda'));
        $cantidad = $request->input('cantidad', 5); // Por defecto 5
        $ordenar = $request->input('ordenar', 'nombre'); // Puede ser 'nombre' o 'fecha'

        $roles = DB::table('roles')
            ->when($busqueda, function ($query, $busqueda) {
                return $query->whereRaw('UPPER(nombre) LIKE ?', ["%{$busqueda}%"]);
            })
            ->when($ordenar === 'fecha', function ($query) {
                return $query->orderBy('created_at', 'desc');
            }, function ($query) {
                return $query->orderBy('nombre', 'asc');
            })
            ->paginate($cantidad)
            ->appends([
                'busqueda' => $busqueda,
                'cantidad' => $cantidad,
                'ordenar' => $ordenar,
            ]);

        return view('permisos.index', compact('roles', 'busqueda', 'cantidad', 'ordenar'));
    }

    /**
     * Guardar o actualizar los permisos de un rol.
     */
    public function guardarPermisos(Request $request)
    {
        $rol_id = $request->input('rol_id');
        $modulos = $request->input('modulos'); // array de objetos con permisos por módulo

        if (!$rol_id || !is_array($modulos)) {
            return response()->json(['error' => 'Datos inválidos'], 400);
        }

        try {
            foreach ($modulos as $mod) {
                $modulo_id = $mod['modulo_id'];

                $existe = DB::table('permisos')
                    ->where('rol_id', $rol_id)
                    ->where('modulo_id', $modulo_id)
                    ->first();

                $datos = [
                    'tiene_acceso' => $mod['tiene_acceso'] ?? false,
                    'puede_crear' => $mod['puede_crear'] ?? false,
                    'puede_actualizar' => $mod['puede_actualizar'] ?? false,
                    'puede_eliminar' => $mod['puede_eliminar'] ?? false,
                    'updated_at' => now(),
                ];

                if ($existe) {
                    DB::table('permisos')
                        ->where('id', $existe->id)
                        ->update($datos);
                } else {
                    DB::table('permisos')->insert(array_merge($datos, [
                        'rol_id' => $rol_id,
                        'modulo_id' => $modulo_id,
                        'created_at' => now()
                    ]));
                }
            }

            return response()->json(['mensaje' => 'Permisos guardados correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al guardar permisos'], 500);
        }
    }
    
}
