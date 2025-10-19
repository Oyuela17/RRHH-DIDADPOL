<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Mostrar lista de roles con búsqueda, paginación y orden dinámico.
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

        return view('roles.index', compact('roles', 'busqueda', 'cantidad', 'ordenar'));
    }

    /**
     * Mostrar formulario de creación de rol.
     */
    public function create()
    {
        return view('roles.create');
    }

    /**
     * Guardar nuevo rol.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'regex:/^[A-Z]+$/', 'unique:roles,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255', 'regex:/^[A-ZÁÉÍÓÚÑ\s]+$/u'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO']
        ]);

        DB::table('roles')->insert([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'estado' => strtoupper($request->estado),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit($id)
    {
        $rol = DB::table('roles')->where('id', $id)->first();

        if (!$rol) {
            return redirect()->route('roles.index')->with('error', 'Rol no encontrado.');
        }

        return view('roles.edit', compact('rol'));
    }

    /**
     * Actualizar rol.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'regex:/^[A-Z]+$/'],
            'descripcion' => ['nullable', 'string', 'max:255', 'regex:/^[A-ZÁÉÍÓÚÑ\s]+$/u'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO']
        ]);

        DB::table('roles')->where('id', $id)->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'estado' => strtoupper($request->estado),
            'updated_at' => now()
        ]);

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    /**
     * Eliminar rol.
     */
    public function destroy($id)
    {
        DB::table('roles')->where('id', $id)->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente.');
    }

    /**
     * Cambiar estado (ACTIVO/INACTIVO).
     */
    public function cambiarEstado(Request $request, $id)
    {
        $estado = strtoupper($request->input('estado'));

        if (!in_array($estado, ['ACTIVO', 'INACTIVO'])) {
            return response()->json(['message' => 'Estado inválido.'], 400);
        }

        $rol = DB::table('roles')->where('id', $id)->first();

        if (!$rol) {
            return response()->json(['message' => 'Rol no encontrado.'], 404);
        }

        DB::table('roles')->where('id', $id)->update([
            'estado' => $estado,
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Estado actualizado correctamente.']);
    }
}
