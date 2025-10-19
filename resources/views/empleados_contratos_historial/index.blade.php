@extends('layouts.app')

@section('title', 'Historial de Contratos')

@section('content')
<div class="empleado-wrapper">
    <h2 class="titulo-empleado">Historial de Contratos</h2>


    <!-- Tabla de contratos -->
    <div class="empleado-container">
        <table class="empleado-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Empleado</th>
                    <th>Puesto</th>
                    <th>Inicio</th>
                    <th>Final</th>
                    <th>Salario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contratos as $contrato)
                    <tr>
                        <td>{{ $contrato->cod_contrato }}</td>
                        <td>{{ $contrato->cod_empleado }}</td>
                        <td>{{ $contrato->cod_puesto }}</td>
                        <td>{{ $contrato->fecha_inicio_contrato }}</td>
                        <td>{{ $contrato->fecha_final_contrato ?? '-' }}</td>
                        <td>L. {{ number_format($contrato->salario, 2) }}</td>
                        <td class="table-actions">
                            <a href="{{ route('empleados-contratos-historial.edit', $contrato->cod_contrato) }}" class="btn btn-outline-primary">
                                Editar
                            </a>
                            <form action="{{ route('empleados-contratos-historial.destroy', $contrato->cod_contrato) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="no-data">No hay contratos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
