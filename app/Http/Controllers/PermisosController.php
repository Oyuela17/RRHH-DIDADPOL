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
        $ordenar  = $request->input('ordenar', 'nombre'); // Puede ser 'nombre' o 'fecha'

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
                'ordenar'  => $ordenar,
            ]);

        return view('permisos.index', compact('roles', 'busqueda', 'cantidad', 'ordenar'));
    }

    /**
     * Guardar o actualizar los permisos de un rol directamente en la BD.
     * Espera en el body:
     *  - rol_id
     *  - modulos: [
     *      {
     *        modulo_id,
     *        tiene_acceso,
     *        puede_crear,
     *        puede_actualizar,
     *        puede_eliminar
     *      }, ...
     *    ]
     */
    public function guardarPermisos(Request $request)
    {
        $rol_id  = (int) $request->input('rol_id');
        $modulos = $request->input('modulos', []);

        if (!$rol_id || !is_array($modulos) || empty($modulos)) {
            return response()->json([
                'ok'    => false,
                'error' => 'Datos inválidos: se requiere rol_id y al menos un módulo.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($modulos as $mod) {
                if (!isset($mod['modulo_id'])) {
                    continue; // si falta modulo_id, se ignora ese elemento
                }

                $modulo_id = (int) $mod['modulo_id'];

                // Normalizar valores a boolean
                $datos = [
                    'tiene_acceso'     => (bool)($mod['tiene_acceso']     ?? false),
                    'puede_crear'      => (bool)($mod['puede_crear']      ?? false),
                    'puede_actualizar' => (bool)($mod['puede_actualizar'] ?? false),
                    'puede_eliminar'   => (bool)($mod['puede_eliminar']   ?? false),
                    'updated_at'       => now(),
                ];

                $existe = DB::table('permisos')
                    ->where('rol_id', $rol_id)
                    ->where('modulo_id', $modulo_id)
                    ->first();

                if ($existe) {
                    DB::table('permisos')
                        ->where('id', $existe->id)
                        ->update($datos);
                } else {
                    DB::table('permisos')->insert(array_merge($datos, [
                        'rol_id'    => $rol_id,
                        'modulo_id' => $modulo_id,
                        'created_at'=> now(),
                    ]));
                }
            }

            DB::commit();

            return response()->json([
                'ok'      => true,
                'mensaje' => 'Permisos guardados correctamente',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'ok'    => false,
                'error' => 'Error al guardar permisos: ' . $e->getMessage(),
            ], 500);
        }
    }
}
