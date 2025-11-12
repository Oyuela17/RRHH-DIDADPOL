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
        display: flex;
        gap: 8px;
        margin-top: 10px;
        flex-wrap: wrap;
    }
    .btn-mode {
        border: none;
        padding: 8px 12px;          /* 🔹 más pequeño */
        font-weight: 600;
        border-radius: 6px;          /* 🔹 más pequeño */
        cursor: pointer;
        transition: transform .05s ease, opacity .2s ease;
        font-size: 0.9rem;           /* 🔹 más pequeño */
        line-height: 1.1rem;
    }
    .btn-mode:active { transform: translateY(1px); }
    .btn-inst { background: #0ea5e9; color: #fff; }     /* azul */
    .btn-pers { background: #f59e0b; color: #fff; }     /* ámbar */
    .btn-submit { display:none; } /* ocultamos el submit clásico */
    .hint { font-size: .9rem; color:#555; margin-top:6px; }
    .muted { color:#666; font-size:12px; }
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

        {{-- Botones de modo (compactos) --}}
        <div class="btn-row">
            <button type="button" class="btn-mode btn-inst" id="btnInst" title="Genera un correo institucional único">
                Crear con correo institucional
            </button>
            <button type="button" class="btn-mode btn-pers" id="btnPers" title="Usa el correo personal escrito">
                Crear con correo personal
            </button>
        </div>

        {{-- Campo oculto para la bandera --}}
        <input type="hidden" id="usar_correo_institucional" value="true">

        {{-- Submit oculto (compatibilidad) --}}
        <button type="submit" class="btn-submit">REGISTRAR USUARIO</button>
    </form>
</div>

{{-- Script --}}
<script>
    const $select = document.getElementById('persona_select');
    const $nombre = document.getElementById('nombre_completo');
    const $correo = document.getElementById('correo_personal');
    const $flag   = document.getElementById('usar_correo_institucional');
    const $form   = document.getElementById('formRegistroUsuario');

    // Autocompletar nombre y correo al elegir persona
    $select.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        $nombre.value = opt.getAttribute('data-nombre') || '';
        $correo.value = (opt.getAttribute('data-correo') || '').trim();
    });

    document.getElementById('btnInst').addEventListener('click', () => {
        $flag.value = 'true';
        enviarRegistro();
    });

    document.getElementById('btnPers').addEventListener('click', () => {
        $flag.value = 'false';
        enviarRegistro();
    });

    // Prevenir submit por Enter
    $form.addEventListener('submit', (e) => e.preventDefault());

    function enviarRegistro() {
        const token = document.querySelector('input[name="_token"]').value;
        const cod_persona = $select.value;                 // 🔹 enviamos cod_persona (no persona_id)
        const nombre_completo = $nombre.value.trim();
        const correo_personal = ($correo.value || '').trim();
        const usar_correo_institucional = ($flag.value === 'true');

        if (!cod_persona || !nombre_completo) {
            Swal.fire({
                icon: 'warning',
                title: 'Faltan datos',
                text: 'Selecciona una persona para continuar.',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        if (!usar_correo_institucional && !correo_personal) {
            Swal.fire({
                icon: 'warning',
                title: 'Correo personal requerido',
                text: 'Para crear con correo personal, se necesita un correo válido.',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        // Confirmación
        const correoFinalPreview = usar_correo_institucional
            ? '(se generará un institucional único)'
            : correo_personal;

        Swal.fire({
            icon: 'question',
            title: 'Confirmar registro',
            html: `
                <div style="text-align:left">
                  <p><b>Persona:</b> ${nombre_completo}</p>
                  <p><b>Modo:</b> ${usar_correo_institucional ? 'Correo institucional' : 'Correo personal'}</p>
                  <p><b>Correo final:</b> ${correoFinalPreview}</p>
                  <p class="muted">El enlace para definir contraseña expira en 24 horas.</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Registrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: usar_correo_institucional ? '#0ea5e9' : '#f59e0b'
        }).then(result => {
            if (!result.isConfirmed) return;

            // Deshabilitar botones durante la petición
            toggleButtons(true);

            fetch("{{ route('usuario.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    cod_persona,            // 🔹 nombre correcto para el backend Node
                    nombre_completo,
                    correo_personal,
                    usar_correo_institucional
                })
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));

                if (res.ok && (data.success === true || data.mensaje)) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Usuario registrado',
                        html: data.mensaje
                            ? data.mensaje
                            : 'Usuario registrado correctamente. Revisa el correo personal.',
                        confirmButtonColor: '#16a34a'
                    });
                    // Limpiar
                    $form.reset();
                    $nombre.value = '';
                    $correo.value = '';
                    return;
                }

                // Errores del backend (mostrar texto exacto si llega)
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
            })
            .finally(() => toggleButtons(false));
        });
    }

    function toggleButtons(disabled) {
        document.getElementById('btnInst').disabled = disabled;
        document.getElementById('btnPers').disabled = disabled;
        document.getElementById('btnInst').style.opacity = disabled ? .7 : 1;
        document.getElementById('btnPers').style.opacity = disabled ? .7 : 1;
    }
</script>
@endsection
