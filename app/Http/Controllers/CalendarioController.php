<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CalendarioController extends Controller
{
    private string $nodeBaseUrl = 'http://localhost:3000/api/eventos';

    private function employeeHeader()
    {
        return [
            'X-Employee-Code' => auth()->user()->empleado->cod_empleado
        ];
    }

    // Mostrar la vista del calendario
    public function index()
    {
        return view('calendario.index');
    }

    // Obtener eventos desde Node.js (solo del usuario)
    public function obtenerEventos()
    {
        try {
            $response = Http::withHeaders($this->employeeHeader())
                ->get($this->nodeBaseUrl);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['error' => 'No se pudo obtener los eventos.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al conectarse con el servidor.'], 500);
        }
    }

    // Crear nuevo evento (con cod_empleado automático)
    public function guardarEvento(Request $request)
    {
        try {
            $response = Http::withHeaders($this->employeeHeader())
                ->post($this->nodeBaseUrl, $request->all());

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['error' => 'No se pudo crear el evento.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al conectarse con el servidor.'], 500);
        }
    }

    // Actualizar evento existente (validando dueño en Node)
    public function actualizarEvento(Request $request, $id)
    {
        try {
            $response = Http::withHeaders($this->employeeHeader())
                ->put("{$this->nodeBaseUrl}/{$id}", $request->all());

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['error' => 'No se pudo actualizar el evento.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al conectarse con el servidor.'], 500);
        }
    }

    // Eliminar evento (solo si pertenece al usuario)
    public function eliminarEvento($id)
    {
        try {
            $response = Http::withHeaders($this->employeeHeader())
                ->delete("{$this->nodeBaseUrl}/{$id}");

            if ($response->successful()) {
                return response()->json(['message' => 'Evento eliminado correctamente']);
            }

            return response()->json(['error' => 'No se pudo eliminar el evento.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al conectarse con el servidor.'], 500);
        }
    }
}
