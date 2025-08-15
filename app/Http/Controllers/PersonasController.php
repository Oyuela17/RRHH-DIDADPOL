<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PersonasController extends Controller
{
    public function index()
    {
        // Llamada a la API
        $response = Http::get('http://localhost:3000/api/personas/detalle');

        if ($response->failed()) {
            return back()->with('error', 'Error al obtener datos de la API');
        }

        // Obtener datos como colección
        $personas = collect($response->json());

        // Pasar todos los datos sin paginación al Blade
        return view('personas.index', compact('personas'));
    }
}
