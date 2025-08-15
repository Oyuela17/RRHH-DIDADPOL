<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NivelEducativoController extends Controller
{
    private $apiUrl = 'http://localhost:3000/api/niveles_educativos';

    public function index()
    {
        return view('niveles_educativos.index');
    }

    public function store(Request $request)
    {
        $response = Http::post($this->apiUrl, [
            'nom_nivel_educativo' => $request->input('nom_nivel_educativo'),
            'descripcion_nivel' => $request->input('descripcion_nivel'),
            'fec_modificacion' => now(),
            'usr_modificacion' => 'admin', // Puedes reemplazar por Auth::user()->name si manejas autenticación
        ]);

        return response()->json($response->json(), $response->status());
    }

    public function update(Request $request, $id)
    {
        $response = Http::put("{$this->apiUrl}/{$id}", [
            'nom_nivel_educativo' => $request->input('nom_nivel_educativo'),
            'descripcion_nivel' => $request->input('descripcion_nivel'),
            'fec_modificacion' => now(),
            'usr_modificacion' => 'admin',
        ]);

        return response()->json($response->json(), $response->status());
    }

    public function destroy($id)
    {
        $response = Http::delete("{$this->apiUrl}/{$id}");
        return response()->json($response->json(), $response->status());
    }
}
