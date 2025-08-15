<table>
    <thead>
        <tr>
            <th>Nombre</th>
            @foreach ($dias as $dia)
                <th>{{ $dia }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($empleados as $emp)
            <tr>
                <td>{{ $emp['nombre'] }}</td>
                @foreach ($dias as $dia)
                    <td>{{ $emp['asistencia'][$dia] ?? '-' }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
