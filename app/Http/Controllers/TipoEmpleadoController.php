<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TipoEmpleadoController extends Controller
{
    private string $apiUrl = 'http://localhost:3000/api/tipos-empleados';

    public function index(Request $request)
    {
        $busqueda = strtoupper((string) $request->input('busqueda', ''));
        $ordenar  = $request->input('ordenar', 'nombre'); // 'nombre'|'fecha'
        $cantidad = (int) $request->input('cantidad', 5);
        $cantidad = $cantidad > 0 ? $cantidad : 5;

        try {
            $response = Http::timeout(10)->get($this->apiUrl.'?detalles=true');
            if (!$response->successful()) {
                return back()->with('error', 'No se pudo obtener la lista de tipos de empleado.');
            }

            $datos = collect($response->json() ?? []);

            if ($busqueda !== '') {
                $datos = $datos->filter(fn($t) =>
                    Str::contains(strtoupper($t['nom_tipo'] ?? ''), $busqueda)
                );
            }

            $datos = $ordenar === 'fecha'
                ? $datos->sortByDesc('fec_registro')
                : $datos->sortBy('nom_tipo');

            $pagina = (int) $request->get('page', 1);
            $items  = $datos->values()->forPage($pagina, $cantidad);

            $paginador = new LengthAwarePaginator(
                $items, $datos->count(), $cantidad, $pagina,
                ['path' => route('tipos.index'), 'query' => $request->query()]
            );

            return view('tipos_empleados.index', [
                'tipos'    => $paginador,
                'busqueda' => $busqueda,
                'ordenar'  => $ordenar,
                'cantidad' => $cantidad
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Servicio no disponible al listar tipos.');
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom_tipo'    => ['required','string','max:30'],
            'descripcion' => ['required','string','max:100'],
        ]);

        $data['usr_registro'] = auth()->user()->name ?? 'admin';

        try {
            $response = Http::timeout(10)->asJson()->post($this->apiUrl, $data);

            if ($response->successful()) {
                return redirect()->route('tipos.index')
                    ->with('success', 'Tipo de empleado registrado correctamente.');
            }

            $msg = $response->json('error') ?? $response->body();
            return back()->with('error', 'Error al registrar: '.$msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Servicio no disponible al registrar.');
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom_tipo'    => ['required','string','max:30'],
            'descripcion' => ['required','string','max:100'],
        ]);

        // Si tu API acepta usr_modificacion, inclúyelo; si no, omite esta línea.
        $data['usr_modificacion'] = auth()->user()->name ?? 'admin';

        try {
            $response = Http::timeout(10)->asJson()->put("{$this->apiUrl}/{$id}", $data);

            if ($response->successful()) {
                return redirect()->route('tipos.index')
                    ->with('success', 'Tipo de empleado actualizado correctamente.');
            }

            $msg = $response->json('error') ?? $response->body();
            return back()->with('error', 'Error al actualizar: '.$msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Servicio no disponible al actualizar.');
        }
    }

    public function destroy($id)
    {
        try {
            $response = Http::timeout(10)->delete("{$this->apiUrl}/{$id}");

            if ($response->successful()) {
                return redirect()->route('tipos.index')
                    ->with('success', 'Tipo de empleado eliminado correctamente.');
            }

            $msg = $response->json('error') ?? $response->body();
            return back()->with('error', 'Error al eliminar: '.$msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Servicio no disponible al eliminar.');
        }
    }
}
