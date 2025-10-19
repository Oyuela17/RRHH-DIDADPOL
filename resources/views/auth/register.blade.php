@extends('layouts.dashboard')

@section('title', 'Registrar Usuario')

@section('content')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .readonly-input {
        background-color: #f5f5f5;
        cursor: not-allowed;
        pointer-events: none;
    }
</style>

<div class="register-user-container">
    <h2>Registro de Usuario</h2>

    {{-- Formulario --}}
    <form id="formRegistroUsuario">
        @csrf

        <div class="form-group">
            <label for="persona_select">Selecciona una persona</label>
            <select name="persona_id" id="persona_select" required>
                <option value="">-- Selecciona --</option>
                @foreach ($empleados as $empleado)
                    <option value="{{ $empleado['cod_persona'] }}"
                        data-nombre="{{ $empleado['nombre_completo'] }}"
                        data-correo="{{ $empleado['email_trabajo'] }}">
                        {{ $empleado['nombre_completo'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="nombre_completo">Nombre completo</label>
            <input type="text" name="nombre_completo" id="nombre_completo" class="readonly-input" readonly required>
        </div>

        <div class="form-group">
            <label for="correo_personal">Correo personal</label>
            <input type="email" name="correo_personal" id="correo_personal" class="readonly-input" readonly required>
        </div>

        <button type="submit" class="btn-submit">REGISTRAR USUARIO</button>
    </form>
</div>

{{-- Script --}}
<script>
    // Autocompletar nombre y correo
    document.getElementById('persona_select').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('nombre_completo').value = selected.getAttribute('data-nombre') || '';
        document.getElementById('correo_personal').value = selected.getAttribute('data-correo') || '';
    });

    // Enviar el formulario vía Fetch sin recargar
    document.getElementById('formRegistroUsuario').addEventListener('submit', function (e) {
        e.preventDefault();

        const token = document.querySelector('input[name="_token"]').value;
        const persona_id = document.getElementById('persona_select').value;
        const nombre_completo = document.getElementById('nombre_completo').value;
        const correo_personal = document.getElementById('correo_personal').value;

        fetch("{{ route('usuario.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                persona_id,
                nombre_completo,
                correo_personal
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Usuario registrado',
                    text: 'Usuario registrado correctamente. Revisa el correo personal.',
                    confirmButtonColor: '#f59e0b'
                });

                // Limpiar campos
                document.getElementById('formRegistroUsuario').reset();
                document.getElementById('nombre_completo').value = '';
                document.getElementById('correo_personal').value = '';
            } else if (data.errors) {
                const errores = Object.values(data.errors).flat().join('<br>');
                Swal.fire({
                    icon: 'error',
                    title: 'Error al registrar',
                    html: errores,
                    confirmButtonColor: '#ff6b35'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error inesperado',
                    text: 'Ocurrió un error inesperado al registrar el usuario.',
                    confirmButtonColor: '#ff6b35'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error de red',
                text: 'No se pudo registrar el usuario. Intenta más tarde.',
                confirmButtonColor: '#ff6b35'
            });
        });
    });
</script>
@endsection
