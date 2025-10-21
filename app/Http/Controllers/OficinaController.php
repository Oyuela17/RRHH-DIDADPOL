<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class OficinaController extends Controller
{
     protected $baseUrl = 'https://rrhh-didadpol-1.onrender.com/api/oficinas';

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
        // Normaliza alias de campos (por si el frontend envía "nombre" en vez de "nom_oficina")
        $input = $request->all();
        if (!isset($input['nom_oficina']) && isset($input['nombre'])) {
            $input['nom_oficina'] = $input['nombre'];
        }

        $validator = Validator::make(
            $input,
            [
                // Solo letras y espacios
                'nom_oficina' => ['required','string','max:80','regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'],
                // Solo números y guiones
                'telefono'    => ['nullable','string','max:20','regex:/^[0-9\-]+$/'],
                // Solo letras y espacios
                'encargado'   => ['nullable','string','max:80','regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'],
            ],
            [
                'nom_oficina.required' => 'El nombre de la oficina es obligatorio.',
                'nom_oficina.regex'    => 'El nombre solo permite letras y espacios.',
                'telefono.regex'       => 'El teléfono solo permite números y guiones.',
                'encargado.regex'      => 'El encargado solo permite letras y espacios.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'advertencia' => 'Revisa los campos: No se aceptan números ni símbolos en Nombre/Encargado y el Teléfono solo permite números y guiones.',
                'errors'      => $validator->errors(),
            ], 422);
        }

        try {
            $response = Http::post($this->baseUrl, $input);
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo registrar la oficina'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Normaliza alias
        $input = $request->all();
        if (!isset($input['nom_oficina']) && isset($input['nombre'])) {
            $input['nom_oficina'] = $input['nombre'];
        }

        $validator = Validator::make(
            $input,
            [
                'nom_oficina' => ['required','string','max:80','regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'],
                'telefono'    => ['nullable','string','max:20','regex:/^[0-9\-]+$/'],
                'encargado'   => ['nullable','string','max:80','regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'],
            ],
            [
                'nom_oficina.required' => 'El nombre de la oficina es obligatorio.',
                'nom_oficina.regex'    => 'El nombre solo permite letras y espacios.',
                'telefono.regex'       => 'El teléfono solo permite números y guiones.',
                'encargado.regex'      => 'El encargado solo permite letras y espacios.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'advertencia' => 'Revisa los campos: No se aceptan números ni símbolos en Nombre/Encargado y el Teléfono solo permite números y guiones.',
                'errors'      => $validator->errors(),
            ], 422);
        }

        try {
            $response = Http::put("{$this->baseUrl}/{$id}", $input);
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