<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DatosEmpresaController extends Controller
{
    public function index()
    {
        $response = Http::get('http://localhost:3000/api/datos_empresa');

        if ($response->successful()) {
            $datos = $response->json();
            return view('datos_empresa.index', compact('datos'));
        } else {
            return back()->with('error', 'No se pudieron obtener los datos de la empresa.');
        }
    }

    
    public function actualizar(Request $request, $id)
    {
        $response = Http::put("http://localhost:3000/api/datos_empresa/{$id}", $request->all());

        if ($response->successful()) {
            return redirect()->route('datos_empresa.index')->with('success', 'Datos actualizados correctamente.');
        } else {
            return redirect()->back()->with('error', 'Error al actualizar los datos.');
        }
    }
}