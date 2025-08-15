<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('busqueda');
        $ordenar = $request->input('ordenar', 'nombre');
        $cantidad = $request->input('cantidad', 5);

        $response = Http::get('http://localhost:3000/api/empleados');
        $empleados = collect($response->json());

        if ($busqueda) {
            $empleados = $empleados->filter(function ($e) use ($busqueda) {
                return stripos($e['nombre_completo'], $busqueda) !== false;
            });
        }

        $empleados = $ordenar === 'fecha'
            ? $empleados->sortByDesc('fecha_contratacion')
            : $empleados->sortBy('nombre_completo');

        $paginaActual = $request->input('page', 1);
        $items = $empleados->values();
        $total = $items->count();
        $paginados = $items->slice(($paginaActual - 1) * $cantidad, $cantidad);
        $empleadosPaginados = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginados,
            $total,
            $cantidad,
            $paginaActual,
            ['path' => route('empleados.index'), 'query' => $request->query()]
        );

        return view('empleados.index', [
            'empleados' => $empleadosPaginados
        ]);
    }

    public function store(Request $request)
    {
        $this->validarEmpleado($request);

        $multipart = $this->crearMultipart($request, 'usr_registro');

        $respuesta = Http::asMultipart()->post('http://localhost:3000/api/empleados', $multipart);

        if ($respuesta->successful()) {
            return redirect()->route('empleados.index')->with('success', 'Empleado registrado correctamente.');
        } else {
            return back()->withErrors(['error' => 'No se pudo registrar el empleado.'])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $this->validarEmpleado($request);

        $multipart = $this->crearMultipart($request, 'usr_modificacion');

        $respuesta = Http::asMultipart()->put("http://localhost:3000/api/empleados/{$id}", $multipart);

        if ($respuesta->successful()) {
            return redirect()->route('empleados.index')->with('success', 'Empleado actualizado correctamente.');
        } else {
            return back()->withErrors(['error' => 'No se pudo actualizar el empleado.'])->withInput();
        }
    }

    public function destroy($id)
    {
        $respuesta = Http::delete("http://localhost:3000/api/empleados/{$id}");

        if ($respuesta->successful()) {
            return redirect()->route('empleados.index')->with('success', 'Empleado eliminado correctamente.');
        } else {
            return redirect()->route('empleados.index')->withErrors(['error' => 'No se pudo eliminar el empleado.']);
        }
    }

    // 🔁 Método para validar campos (reutilizado)
    private function validarEmpleado(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:100',
            'dni' => 'required|string|max:20',
            'email_trabajo' => 'nullable|email',
            'telefono' => 'required|string',
            'direccion' => 'required|string',
            'cod_municipio' => 'required|integer',
            'genero' => 'required|string',
            'estado_civil' => 'required|string',
            'fec_nacimiento' => 'required|date',
            'lugar_nacimiento' => 'required|string',
            'nacionalidad' => 'required|string',
            'nombre_contacto_emergencia' => 'required|string',
            'telefono_emergencia' => 'required|string',
            'cod_tipo_modalidad' => 'required|integer',
            'cod_puesto' => 'required|integer',
            'cod_nivel_educativo' => 'required|integer',
            'cod_oficina' => 'required|integer',
            'cod_horario' => 'required|integer',
            'fecha_contratacion' => 'required|date',
            'salario' => 'required|numeric',
            'fecha_inicio_contrato' => 'required|date',
            'fecha_final_contrato' => 'nullable|date',
            'contrato_activo' => 'required|boolean',
            'cod_terminacion_contrato' => 'nullable|integer',
            'cod_tipo_empleado' => 'required|integer',
            'es_jefe' => 'required|boolean',
            'foto_persona' => 'nullable|image|max:2048',
        ]);
    }

    // 📦 Método para crear multipart (reutilizado para store y update)
    private function crearMultipart(Request $request, $usuarioCampo)
    {
        $multipart = [];

        if ($request->hasFile('foto_persona')) {
            $foto = $request->file('foto_persona');
            $multipart[] = [
                'name' => 'foto_persona',
                'contents' => fopen($foto->getPathname(), 'r'),
                'filename' => $foto->getClientOriginalName(),
            ];
        }

        $campos = $request->except('foto_persona');
        $campos[$usuarioCampo] = auth()->user()->name ?? 'ADMIN';

        foreach ($campos as $key => $value) {
            $multipart[] = [
                'name' => $key,
                'contents' => is_bool($value) ? ($value ? 'true' : 'false') : (string) $value
            ];
        }

        return $multipart;
    }
}
