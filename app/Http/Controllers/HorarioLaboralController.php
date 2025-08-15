<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;

class HorarioLaboralController extends Controller
{
    private $apiUrl = 'http://localhost:3000/api/horarios';

    public function index(Request $request)
    {
        $busqueda = strtoupper($request->input('busqueda'));
        $ordenar = $request->input('ordenar', 'nombre');
        $cantidad = $request->input('cantidad', 5);

        // Obtener todos los horarios desde la API
        $response = Http::get($this->apiUrl . '?detalles=true');

        if (!$response->successful()) {
            return redirect()->back()->with('error', 'No se pudo obtener la lista de horarios.');
        }

        $datos = collect($response->json());

        // Filtro de búsqueda
        if ($busqueda) {
            $datos = $datos->filter(fn($h) =>
                str_contains(strtoupper($h['nom_horario']), $busqueda)
            );
        }

        // Ordenamiento
        if ($ordenar === 'fecha') {
            $datos = $datos->sortByDesc('fec_registro');
        } else {
            $datos = $datos->sortBy('nom_horario');
        }

        // Paginación manual
        $paginaActual = $request->get('page', 1);
        $horarios = $datos->values()->forPage($paginaActual, $cantidad);
        $paginador = new LengthAwarePaginator(
            $horarios,
            $datos->count(),
            $cantidad,
            $paginaActual,
            ['path' => route('horarios.index'), 'query' => $request->query()]
        );

        return view('horarios_laborales.index', ['horarios' => $paginador]);
    }

    public function store(Request $request)
    {
        $data = $request->only(['nom_horario', 'hora_inicio', 'hora_final']);

        $dias = $request->input('dias_semana');
        $data['dias_semana'] = is_string($dias)
            ? array_map('trim', explode(',', $dias))
            : $dias;

        $data['usr_registro'] = auth()->user()->name ?? 'admin';

        $response = Http::post($this->apiUrl, $data);

        return $response->successful()
            ? redirect()->route('horarios.index')->with('success', 'Horario creado correctamente.')
            : redirect()->route('horarios.index')->with('error', 'Error al crear el horario.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->only(['nom_horario', 'hora_inicio', 'hora_final']);

        $dias = $request->input('dias_semana');
        $data['dias_semana'] = is_string($dias)
            ? array_map('trim', explode(',', $dias))
            : $dias;

        $response = Http::put("{$this->apiUrl}/{$id}", $data);

        return $response->successful()
            ? redirect()->route('horarios.index')->with('success', 'Horario actualizado correctamente.')
            : redirect()->route('horarios.index')->with('error', 'Error al actualizar el horario.');
    }

    public function destroy($id)
    {
        $response = Http::delete("{$this->apiUrl}/{$id}");

        return $response->successful()
            ? redirect()->route('horarios.index')->with('success', 'Horario eliminado correctamente.')
            : redirect()->route('horarios.index')->with('error', 'Error al eliminar el horario.');
    }
}
