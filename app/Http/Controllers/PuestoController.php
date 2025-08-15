<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PuestoController extends Controller
{
    protected $apiUrl = 'http://localhost:3000/api/puestos';

    public function index()
    {
        return view('puestos.index');
    }

    public function store(Request $request)
    {
        try {
            $response = Http::post($this->apiUrl, [
                'nom_puesto' => $request->input('nom_puesto'),
                'funciones_puesto' => $request->input('funciones_puesto'),
                'sueldo_base' => $request->input('sueldo_base'),
                'fec_registro' => now()->toDateTimeString(),
                'usr_registro' => auth()->user()->name ?? 'admin',
                'cod_fuente_financiamiento' => $request->input('cod_fuente_financiamiento') ?? 1
            ]);

            if ($response->successful()) {
                return response()->json(['mensaje' => 'Puesto registrado correctamente']);
            } else {
                return response()->json(['error' => 'Error al registrar el puesto'], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error de conexión: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $response = Http::put("{$this->apiUrl}/{$id}", [
                'nom_puesto' => $request->input('nom_puesto'),
                'funciones_puesto' => $request->input('funciones_puesto'),
                'sueldo_base' => $request->input('sueldo_base'),
                'fec_registro' => now()->toDateTimeString(),
                'usr_registro' => auth()->user()->name ?? 'admin',
                'cod_fuente_financiamiento' => $request->input('cod_fuente_financiamiento') ?? 1
            ]);

            if ($response->successful()) {
                return response()->json(['mensaje' => 'Puesto actualizado correctamente']);
            } else {
                return response()->json(['error' => 'Error al actualizar el puesto'], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error de conexión: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $response = Http::delete("{$this->apiUrl}/{$id}");

            if ($response->successful()) {
                return response()->json(['mensaje' => 'Puesto eliminado correctamente']);
            } else {
                return response()->json(['error' => 'Error al eliminar el puesto'], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error de conexión: ' . $e->getMessage()], 500);
        }
    }
}
