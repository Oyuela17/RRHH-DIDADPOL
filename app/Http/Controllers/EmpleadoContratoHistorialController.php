<?php

namespace App\Http\Controllers;

use App\Models\EmpleadoContratoHistorial;
use Illuminate\Http\Request;

class EmpleadoContratoHistorialController extends Controller
{
    public function index()
    {
        $contratos = EmpleadoContratoHistorial::all();
        return view('empleados_contratos_historial.index', compact('contratos'));
    }

    public function create()
    {
        return view('empleados_contratos_historial.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'cod_empleado' => 'required|integer',
            'cod_tipo_empleado' => 'required|integer',
            'cod_puesto' => 'required|integer',
            'fecha_inicio_contrato' => 'required|date',
            'salario' => 'required|numeric',
        ]);

        EmpleadoContratoHistorial::create($request->all());

        return redirect()->route('empleados-contratos-historial.index')
                         ->with('success', 'Contrato creado correctamente.');
    }

    public function edit($id)
    {
        $contrato = EmpleadoContratoHistorial::findOrFail($id);
        return view('empleados_contratos_historial.edit', compact('contrato'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cod_empleado' => 'required|integer',
            'cod_tipo_empleado' => 'required|integer',
            'cod_puesto' => 'required|integer',
            'fecha_inicio_contrato' => 'required|date',
            'salario' => 'required|numeric',
        ]);

        $contrato = EmpleadoContratoHistorial::findOrFail($id);
        $contrato->update($request->all());

        return redirect()->route('empleados-contratos-historial.index')
                         ->with('success', 'Contrato actualizado correctamente.');
    }

    public function destroy($id)
    {
        $contrato = EmpleadoContratoHistorial::findOrFail($id);
        $contrato->delete();

        return redirect()->route('empleados-contratos-historial.index')
                         ->with('success', 'Contrato eliminado correctamente.');
    }
}
