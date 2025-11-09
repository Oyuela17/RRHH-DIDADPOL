<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PermisosController extends Controller
{
    /**
     * Listado de roles con búsqueda, orden y paginación.
     */
    public function index(Request $request)
    {
        $busqueda = strtoupper((string) $request->input('busqueda', ''));
        $cantidad = (int) $request->input('cantidad', 5);   // por defecto 5
        $ordenar  = $request->input('ordenar', 'nombre');   // 'nombre' | 'fecha'

        $roles = DB::table('roles')
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                return $q->whereRaw('UPPER(nombre) LIKE ?', ["%{$busqueda}%"]);
            })
            ->when($ordenar === 'fecha', fn($q) => $q->orderBy('created_at', 'desc'),
                                fn($q) => $q->orderBy('nombre', 'asc'))
            ->paginate($cantidad)
            ->appends([
                'busqueda' => $busqueda,
                'cantidad' => $cantidad,
                'ordenar'  => $ordenar,
            ]);

        return view('permisos.index', compact('roles', 'busqueda', 'cantidad', 'ordenar'));
    }

    /**
     * (Opcional útil) Traer permisos visibles por un rol (para la UI).
     * Devuelve todos los módulos con flags; si el rol no tiene fila en permisos,
     * los flags vienen en false.
     */
    public function listarPorRol(Request $request, int $rol_id)
    {
        $data = DB::table('modulos as m')
            ->leftJoin('permisos as p', function ($j) use ($rol_id) {
                $j->on('p.modulo_id', '=', 'm.id')
                  ->where('p.rol_id', '=', $rol_id);
            })
            ->selectRaw("
                m.id   as modulo_id,
                m.nombre,
                COALESCE(p.tiene_acceso, false)      as tiene_acceso,
                COALESCE(p.puede_crear, false)       as puede_crear,
                COALESCE(p.puede_actualizar, false)  as puede_actualizar,
                COALESCE(p.puede_eliminar, false)    as puede_eliminar
            ")
            ->orderBy('m.nombre')
            ->get();

        return response()->json($data);
    }

    /**
     * Guardar o actualizar los permisos de un rol.
     * Espera:
     *  - rol_id: int
     *  - modulos: array de objetos { modulo_id, tiene_acceso, puede_crear, puede_actualizar, puede_eliminar }
     */
    public function guardarPermisos(Request $request)
    {
        // 1) Validación
        $v = Validator::make($request->all(), [
            'rol_id'   => 'required|integer|exists:roles,id',
            'modulos'  => 'required|array|min:1',
            'modulos.*.modulo_id'        => 'required|integer|exists:modulos,id',
            'modulos.*.tiene_acceso'     => 'nullable|boolean',
            'modulos.*.puede_crear'      => 'nullable|boolean',
            'modulos.*.puede_actualizar' => 'nullable|boolean',
            'modulos.*.puede_eliminar'   => 'nullable|boolean',
        ], [
            'rol_id.required' => 'El rol es obligatorio.',
            'rol_id.exists'   => 'El rol no existe.',
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()->first()], 422);
        }

        $rol_id  = (int) $request->input('rol_id');
        $modulos = $request->input('modulos', []);

        // 2) Preparar registros para upsert
        $ahora = now();
        $rows  = [];
        foreach ($modulos as $mod) {
            $rows[] = [
                'rol_id'           => $rol_id,
                'modulo_id'        => (int) $mod['modulo_id'],
                'tiene_acceso'     => (bool) ($mod['tiene_acceso']     ?? false),
                'puede_crear'      => (bool) ($mod['puede_crear']      ?? false),
                'puede_actualizar' => (bool) ($mod['puede_actualizar'] ?? false),
                'puede_eliminar'   => (bool) ($mod['puede_eliminar']   ?? false),
                'created_at'       => $ahora,
                'updated_at'       => $ahora,
            ];
        }

        // Recomendación DB: tener índice único (rol_id, modulo_id) en 'permisos'
        // ALTER TABLE public.permisos ADD CONSTRAINT permisos_unq UNIQUE (rol_id, modulo_id);

        // 3) Upsert transaccional
        DB::beginTransaction();
        try {
            // Nota: en Postgres, 'upsert' de Eloquent/Query Builder requiere Laravel 8.32+.
            DB::table('permisos')->upsert(
                $rows,
                ['rol_id', 'modulo_id'], // uniqueBy
                // columnas a actualizar si existe
                ['tiene_acceso', 'puede_crear', 'puede_actualizar', 'puede_eliminar', 'updated_at']
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            // Log opcional
            // \Log::error('Error al guardar permisos', ['e' => $e->getMessage()]);
            return response()->json(['error' => 'Error al guardar permisos'], 500);
        }

        // 4) Invalidar cualquier cacheo/sesión que alimente el menú
        // (por si en alguna parte se usa sesión/caché para $modulosPermitidos)
        try {
            session()->forget('modulosPermitidos');
            if (auth()->check()) {
                cache()->forget('mods_user_'.auth()->id());
            }
        } catch (\Throwable $e) {
            // silencio: si no hay sesión/cache no pasa nada
        }

        // 5) (Opcional) devolver lo que quedó guardado para refrescar la UI sin reconsultas
        $refresco = DB::table('modulos as m')
            ->leftJoin('permisos as p', function ($j) use ($rol_id) {
                $j->on('p.modulo_id', '=', 'm.id')
                  ->where('p.rol_id', '=', $rol_id);
            })
            ->selectRaw("
                m.id   as modulo_id,
                m.nombre,
                COALESCE(p.tiene_acceso, false)      as tiene_acceso,
                COALESCE(p.puede_crear, false)       as puede_crear,
                COALESCE(p.puede_actualizar, false)  as puede_actualizar,
                COALESCE(p.puede_eliminar, false)    as puede_eliminar
            ")
            ->orderBy('m.nombre')
            ->get();

        return response()->json([
            'mensaje'  => 'Permisos guardados correctamente',
            'permisos' => $refresco,
        ], 200);
    }
}
