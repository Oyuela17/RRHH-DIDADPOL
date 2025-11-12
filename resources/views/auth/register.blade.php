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
    .btn-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 12px;
    }
    .btn-mode {
        width: 100%;
        border: none;
        padding: 12px 16px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: transform .05s ease;
    }
    .btn-mode:active { transform: translateY(1px); }
    .btn-inst { background: #0ea5e9; color: #fff; }     /* azul */
    .btn-pers { background: #f59e0b; color: #fff; }     /* ámbar */
    .btn-submit { display:none; } /* ocultamos el submit clásico */
    .hint { font-size: .9rem; color:#555; margin-top:6px; }
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
            <div class="hint">Selecciona a la persona para autocompletar nombre y correo personal.</div>
        </div>

        <div class="form-group">
            <label for="nombre_completo">Nombre completo</label>
            <input type="text" name="nombre_completo" id="nombre_completo" class="readonly-input" readonly required>
        </div>

        <div class="form-group">
            <label for="correo_personal">Correo personal</label>
            <input type="email" name="correo_personal" id="correo_personal" class="readonly-input" readonly required>
            <div class="hint">El enlace para definir contraseña se enviará a este correo personal.</div>
        </div>

        {{-- Botones de modo --}}
        <div class="btn-row">
            <button type="button" class="btn-mode btn-inst" id="btnInst">
                Crear con correo institucional
            </button>
            <button type="button" class="btn-mode btn-pers" id="btnPers">
                Crear con correo personal
            </button>
        </div>

        {{-- Campo oculto para la bandera --}}
        <input type="hidden" id="usar_correo_institucional" value="true">

        {{-- Botón submit tradicional (oculto) para compatibilidad si lo necesitases) --}}
        <button type="submit" class="btn-submit">REGISTRAR USUARIO</button>
    </form>
</div>

{{-- Script --}}
<script>
    // Autocompletar nombre y correo al elegir persona
    document.getElementById('persona_select').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('nombre_completo').value = selected.getAttribute('data-nombre') || '';
        document.getElementById('correo_personal').value = selected.getAttribute('data-correo') || '';
    });

    // Handlers de los dos botones (institucional / personal)
    document.getElementById('btnInst').addEventListener('click', () => {
        document.getElementById('usar_correo_institucional').value = 'true';
        enviarRegistro();
    });

    document.getElementById('btnPers').addEventListener('click', () => {
        document.getElementById('usar_correo_institucional').value = 'false';
        enviarRegistro();
    });

    // Si alguien usa Enter, evitamos submit vacío
    document.getElementById('formRegistroUsuario').addEventListener('submit', function (e) {
        e.preventDefault();
    });

    function enviarRegistro() {
        const token = document.querySelector('input[name="_token"]').value;
        const persona_id = document.getElementById('persona_select').value;
        const nombre_completo = document.getElementById('nombre_completo').value;
        const correo_personal = document.getElementById('correo_personal').value;
        const usar_correo_institucional = document.getElementById('usar_correo_institucional').value === 'true';

        if (!persona_id || !nombre_completo || !correo_personal) {
            Swal.fire({
                icon: 'warning',
                title: 'Faltan datos',
                text: 'Selecciona una persona para continuar.',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        // Confirmación rápida
        const correoFinalPreview = usar_correo_institucional
            ? '(institucional único será generado)'
            : correo_personal;

        Swal.fire({
            icon: 'question',
            title: 'Confirmar registro',
            html: `
                <div style="text-align:left">
                  <p><b>Persona:</b> ${nombre_completo}</p>
                  <p><b>Modo:</b> ${usar_correo_institucional ? 'Correo institucional' : 'Correo personal'}</p>
                  <p><b>Correo final:</b> ${correoFinalPreview}</p>
                  <p style="color:#666;font-size:12px">El enlace para definir contraseña expira en 24 horas.</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Registrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: usar_correo_institucional ? '#0ea5e9' : '#f59e0b'
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch("{{ route('usuario.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    persona_id,
                    nombre_completo,
                    correo_personal,
                    usar_correo_institucional
                })
            })
            .then(async res => {
                const data = await res.json();

                // Éxito (soporta respuestas tipo {success:true} o {mensaje: '...'})
                if (res.ok && (data.success === true || data.mensaje)) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Usuario registrado',
                        html: data.mensaje
                            ? data.mensaje
                            : 'Usuario registrado correctamente. Revisa el correo personal.',
                        confirmButtonColor: '#16a34a'
                    });

                    // Limpiar campos
                    document.getElementById('formRegistroUsuario').reset();
                    document.getElementById('nombre_completo').value = '';
                    document.getElementById('correo_personal').value = '';
                    return;
                }

                // Errores conocidos desde backend
                const msg = data?.error || 'Ocurrió un error al registrar.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error al registrar',
                    text: msg,
                    confirmButtonColor: '#ef4444'
                });
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de red',
                    text: 'No se pudo registrar el usuario. Intenta más tarde.',
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    }
</script>
@endsection
