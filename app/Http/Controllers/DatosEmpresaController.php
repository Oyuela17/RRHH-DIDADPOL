<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class DatosEmpresaController extends Controller
{
    public function index()
    {
        $response = Http::withHeaders([
            'X-User-Id'       => (string) (Auth::id() ?? ''),
            'X-Forwarded-For' => request()->ip(),
        ])->get('https://rrhh-didadpol-1.onrender.com/api/datos_empresa');

        if ($response->successful()) {
            $datos = $response->json();
            return view('datos_empresa.index', compact('datos'));
        } else {
            return back()->with('error', 'No se pudieron obtener los datos de la empresa.');
        }
    }

    public function actualizar(Request $request, $id)
    {
        $response = Http::withHeaders([
            'X-User-Id'       => (string) (Auth::id() ?? ''),
            'X-Forwarded-For' => $request->ip(),
        ])->put("https://rrhh-didadpol-1.onrender.com/api/datos_empresa/{$id}", $request->all());

        if ($response->successful()) {
            return redirect()->route('datos_empresa.index')->with('success', 'Datos actualizados correctamente.');
        } else {
            return redirect()->back()->with('error', 'Error al actualizar los datos.');
        }
    }
}
