<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PuestoController extends Controller
{
    protected $apiUrl = 'https://rrhh-didadpol-1.onrender.com/api/puestos';

    public function index()
    {
        return view('puestos.index');
    }

    public function store(Request $request)
    {
        // 🔹 Validaciones
        $request->validate([
            'nom_puesto' => ['required', 'string', 'max:50', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'],
            'funciones_puesto' => ['required', 'string', 'max:200'],
            'sueldo_base' => ['required', 'numeric', 'min:0']
        ]);

        // 🔹 Verificar manualmente que no tenga números ni símbolos (por seguridad extra)
        if (preg_match('/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/', $request->nom_puesto)) {
            return back()->with('advertencia', 'No se aceptan números ni símbolos en el nombre del puesto.');
        }

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
                return redirect()->route('puestos.index')
                    ->with('success', 'Puesto registrado correctamente.');
            } else {
                return back()->with('error', 'Error al registrar el puesto.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error de conexión: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        // 🔹 Validaciones
        $request->validate([
            'nom_puesto' => ['required', 'string', 'max:50', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'],
            'funciones_puesto' => ['required', 'string', 'max:200'],
            'sueldo_base' => ['required', 'numeric', 'min:0']
        ]);

        if (preg_match('/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/', $request->nom_puesto)) {
            return back()->with('advertencia', 'No se aceptan números ni símbolos en el nombre del puesto.');
        }

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
                return redirect()->route('puestos.index')
                    ->with('success', 'Puesto actualizado correctamente.');
            } else {
                return back()->with('error', 'Error al actualizar el puesto.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error de conexión: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $response = Http::delete("{$this->apiUrl}/{$id}");

            if ($response->successful()) {
                return redirect()->route('puestos.index')
                    ->with('success', 'Puesto eliminado correctamente.');
            } else {
                return back()->with('error', 'Error al eliminar el puesto.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error de conexión: ' . $e->getMessage());
        }
    }
}