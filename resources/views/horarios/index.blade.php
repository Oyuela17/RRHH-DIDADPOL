@extends('layouts.app')
@vite(['resources/css/empleado.css'])

@section('title', 'Horarios Laborales')

@section('content')
<div class="empleado-wrapper">
    <h2 class="titulo-empleado">Horarios Laborales</h2>

    <div class="empleado-container">
        <table class="empleado-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Hora Inicio</th>
                    <th>Hora Final</th>
                    <th>Días</th>
                    <th>Registro de usuario</th>
                    <th>Fecha de Registro</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($horarios as $horario)
                    <tr>
                        <td>{{ $horario->nom_horario }}</td>
                        <td>{{ $horario->hora_inicio }}</td>
                        <td>{{ $horario->hora_final }}</td>
                        <td>
                            @if(is_array($horario->dias_semana))
                                {{ implode(', ', $horario->dias_semana) }}
                            @else
                                {{ $horario->dias_semana }}
                            @endif
                        </td>
                        <td>{{ $horario->usr_registro }}</td>
                        <td>{{ \Carbon\Carbon::parse($horario->fec_registro)->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
