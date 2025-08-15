<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TitulosController extends Controller
{
    private string $apiUrl = 'http://localhost:3000/api/titulos';

    // LISTAR Y BUSCAR
    public function index(Request $request)
    {
        $busqueda = strtoupper((string) $request->input('busqueda', ''));
        $ordenar  = $request->input('ordenar', 'titulo'); // 'titulo' | 'fecha'
        $cantidad = (int) $request->input('cantidad', 5);
        $cantidad = $cantidad > 0 ? $cantidad : 5;

        try {
            $response = Http::timeout(10)->get($this->apiUrl.'?detalles=true');

            if (!$response->successful()) {
                return back()->with('error', 'No se pudo obtener la lista de títulos.');
            }

            $datos = collect($response->json() ?? [])->map(fn($t) => (object) $t);

            // Filtro por búsqueda
            if ($busqueda !== '') {
                $datos = $datos->filter(fn($t) =>
                    Str::contains(strtoupper($t->titulo ?? ''), $busqueda)
                );
            }

            // Ordenamiento
            if ($ordenar === 'fecha') {
                $datos = $datos->sortByDesc('fec_registro');
            } else {
                $datos = $datos->sortBy('titulo');
            }

            // Paginación manual
            $paginaActual = (int) $request->get('page', 1);
            $itemsPagina  = $datos->values()->forPage($paginaActual, $cantidad);

            $paginador = new LengthAwarePaginator(
                $itemsPagina,
                $datos->count(),
                $cantidad,
                $paginaActual,
                ['path' => route('titulos.index'), 'query' => $request->query()]
            );

            return view('titulos.index', [
                'titulos'  => $paginador,
                'busqueda' => $busqueda,
                'ordenar'  => $ordenar,
                'cantidad' => $cantidad
            ]);

        } catch (\Throwable $e) {
            return back()->with('error', 'Servicio no disponible al listar títulos.');
        }
    }

    // REGISTRAR NUEVO TÍTULO
    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo'      => ['required','string','max:100'],
            'abreviatura' => ['nullable','string','max:20'],
            'descripcion' => ['nullable','string','max:255'],
        ]);

        try {
            $response = Http::timeout(10)->asJson()->post($this->apiUrl, $data);

            if ($response->successful()) {
                return redirect()->route('titulos.index')->with('success', 'Título registrado correctamente.');
            }

            $msg = $response->json('error') ?? $response->body();
            return back()->with('error', 'Error al registrar el título: '.$msg);

        } catch (\Throwable $e) {
            return back()->with('error', 'Servicio no disponible al registrar título.');
        }
    }

    // ACTUALIZAR TÍTULO
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'titulo'      => ['required','string','max:100'],
            'abreviatura' => ['nullable','string','max:20'],
            'descripcion' => ['nullable','string','max:255'],
        ]);

        try {
            $response = Http::timeout(10)->asJson()->put($this->apiUrl.'/'.$id, $data);

            if ($response->successful()) {
                return redirect()->route('titulos.index')->with('success', 'Título actualizado correctamente.');
            }

            $msg = $response->json('error') ?? $response->body();
            return back()->with('error', 'Error al actualizar el título: '.$msg);

        } catch (\Throwable $e) {
            return back()->with('error', 'Servicio no disponible al actualizar título.');
        }
    }

    // ELIMINAR TÍTULO
    public function destroy($id)
    {
        try {
            $response = Http::timeout(10)->delete($this->apiUrl.'/'.$id);

            if ($response->successful()) {
                return redirect()->route('titulos.index')->with('success', 'Título eliminado correctamente.');
            }

            $msg = $response->json('error') ?? $response->body();
            return back()->with('error', 'Error al eliminar el título: '.$msg);

        } catch (\Throwable $e) {
            return back()->with('error', 'Servicio no disponible al eliminar título.');
        }
    }
}
