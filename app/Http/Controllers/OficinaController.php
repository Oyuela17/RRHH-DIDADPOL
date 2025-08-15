<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OficinaController extends Controller
{
    protected $baseUrl = 'http://localhost:3000/api/oficinas';

    public function index()
    {
        return view('oficinas.index');
    }

    public function listar()
    {
        try {
            $response = Http::get($this->baseUrl . '?detalles=true');
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al conectar con la API de oficinas'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $response = Http::post($this->baseUrl, $request->all());
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo registrar la oficina'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $response = Http::put("{$this->baseUrl}/{$id}", $request->all());
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo actualizar la oficina'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $response = Http::delete("{$this->baseUrl}/{$id}");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo eliminar la oficina'], 500);
        }
    }
}
