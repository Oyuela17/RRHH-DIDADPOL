@extends('layouts.auth')

@section('title', 'Definir Contraseña')

@section('content')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="form-box">
    <h2 class="titulo">Definir Nueva Contraseña</h2>
    <div class="linea"></div>

    <form id="formDefinir" class="formulario" onsubmit="definirContrasena(event)">
        <input type="hidden" id="token" value="{{ request()->get('token') }}">

        <div class="form-group">
            <label for="password">Nueva contraseña:</label>
            <div class="password-wrapper">
                <input type="password" id="password" required>
                <span toggle="#password" class="toggle-password">
                    <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </span>
            </div>
            <ul class="requisitos">
                <li id="req-length">✔ Mínimo 8 caracteres</li>
                <li id="req-uppercase">✔ Una letra mayúscula</li>
                <li id="req-lowercase">✔ Una letra minúscula</li>
                <li id="req-number">✔ Un número</li>
                <li id="req-symbol">✔ Un símbolo especial</li>
            </ul>
            <div id="barra-seguridad" class="barra-seguridad">
                <div id="nivel-seguridad" class="nivel-seguridad"></div>
            </div>
            <div id="texto-seguridad" class="texto-seguridad"></div>
            <div id="mensajeSeguridad" class="mensaje error"></div>
        </div>

        <div class="form-group">
            <label for="confirmar">Confirmar contraseña:</label>
            <div class="password-wrapper">
                <input type="password" id="confirmar" required>
                <span toggle="#confirmar" class="toggle-password">
                    <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </span>
            </div>
            <div id="coincidencia" class="mensaje"></div>
        </div>

        <button type="submit" id="btnGuardar" disabled>GUARDAR CONTRASEÑA</button>
        <div id="mensaje" class="mensaje"></div>
    </form>
</div>

<style>
body {
    background: linear-gradient(to bottom, #043668, #0a5fa3);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}
.form-box {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
    width: 100%;
    max-width: 420px;
}
.titulo {
    font-size: 24px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 10px;
    color: #222;
}
.linea {
    width: 60px;
    height: 4px;
    background-color: #f9b233;
    margin: 0 0 20px 0;
    border-radius: 2px;
}
.formulario {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.form-group {
    width: 100%;
    margin-bottom: 18px;
}
label {
    font-weight: 600;
    color: #444;
    margin-bottom: 6px;
    display: block;
}
.password-wrapper {
    position: relative;
}
.toggle-password {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.icon-eye {
    width: 20px;
    height: 20px;
    pointer-events: none;
}
input[type="password"], input[type="text"] {
    width: 100%;
    padding: 12px 14px;
    font-size: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    box-sizing: border-box;
}
input[type="password"]:focus, input[type="text"]:focus {
    outline: none;
    border-color: #f9b233;
    box-shadow: 0 0 0 2px rgba(249, 178, 51, 0.2);
}
.requisitos {
    list-style: none;
    padding: 0;
    margin: 10px 0;
    font-size: 13px;
    color: #444;
}
.requisitos li {
    margin-bottom: 4px;
}
#btnGuardar {
    width: 100%;
    background-color: #f9b233;
    color: white;
    font-weight: bold;
    font-size: 15px;
    border: none;
    padding: 14px;
    border-radius: 6px;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}
#btnGuardar:hover {
    background-color: #e3a926;
}
.mensaje {
    margin-top: 6px;
    text-align: center;
    font-weight: 600;
    font-size: 13px;
}
.mensaje.success {
    color: green;
}
.mensaje.error {
    color: red;
}
.barra-seguridad {
    height: 10px;
    background-color: #e0e0e0;
    border-radius: 6px;
    overflow: hidden;
    margin-top: 8px;
}
.nivel-seguridad {
    height: 100%;
    width: 0%;
    background-color: red;
    transition: width 0.4s ease;
}
.texto-seguridad {
    margin-top: 6px;
    font-size: 13px;
    font-weight: bold;
    color: #555;
}
</style>

<script>
document.querySelectorAll('.toggle-password').forEach(toggle => {
    toggle.addEventListener('click', function () {
        const input = document.querySelector(this.getAttribute('toggle'));
        const isVisible = input.type === 'text';
        input.type = isVisible ? 'password' : 'text';
        this.innerHTML = isVisible
            ? `<svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>`
            : `<svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.4 21.4 0 0 1 5.29-6.71"/><path d="M1 1l22 22"/></svg>`;
    });
});

const passwordInput = document.getElementById('password');
const confirmarInput = document.getElementById('confirmar');
const nivelSeguridad = document.getElementById('nivel-seguridad');
const textoSeguridad = document.getElementById('texto-seguridad');
const btnGuardar = document.getElementById('btnGuardar');
const coincidencia = document.getElementById('coincidencia');
const mensajeSeguridad = document.getElementById('mensajeSeguridad');

function validarPassword() {
    const val = passwordInput.value;
    const confirmVal = confirmarInput.value;
    let strength = 0;

    const reqs = [
        [val.length >= 8, 'req-length'],
        [/[A-Z]/.test(val), 'req-uppercase'],
        [/[a-z]/.test(val), 'req-lowercase'],
        [/\d/.test(val), 'req-number'],
        [/[^A-Za-z0-9]/.test(val), 'req-symbol']
    ];

    strength = reqs.reduce((acc, [passed]) => passed ? acc + 1 : acc, 0);
    reqs.forEach(([passed, id]) => {
        const li = document.getElementById(id);
        if (li) li.style.color = passed ? 'green' : 'gray';
    });

    let width = '0%';
    let color = 'red';
    let label = 'Seguridad: Débil';
    if (strength <= 2) {
        width = '33%'; color = '#e74c3c'; label = 'Seguridad: Débil';
    } else if (strength === 3 || strength === 4) {
        width = '66%'; color = '#f39c12'; label = 'Seguridad: Media';
    } else if (strength === 5) {
        width = '100%'; color = '#27ae60'; label = 'Seguridad: Fuerte';
    }
    nivelSeguridad.style.width = width;
    nivelSeguridad.style.backgroundColor = color;
    textoSeguridad.textContent = label;
    textoSeguridad.style.color = color;

    if (val && confirmVal && val !== confirmVal) {
        coincidencia.textContent = '❌ Las contraseñas no coinciden';
        coincidencia.className = 'mensaje error';
    } else {
        coincidencia.textContent = '';
    }

    if (val && strength < 5) {
        mensajeSeguridad.textContent = '❌ Debe cumplir todos los requisitos de seguridad';
    } else {
        mensajeSeguridad.textContent = '';
    }

    btnGuardar.disabled = !(strength === 5 && val === confirmVal);
}

passwordInput.addEventListener('input', validarPassword);
confirmarInput.addEventListener('input', validarPassword);

async function definirContrasena(e) {
    e.preventDefault();
    const password = passwordInput.value;
    const confirmar = confirmarInput.value;
    const token = document.getElementById('token').value;
    const urlParams = new URLSearchParams(window.location.search);
    const email = urlParams.get('email');

    if (!token || !email) {
        Swal.fire({ icon: 'error', title: 'Faltan datos', text: 'Token o correo faltante.' });
        return;
    }

    if (password !== confirmar) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Las contraseñas no coinciden.' });
        return;
    }

    try {
        const response = await fetch("http://localhost:3000/api/definir-contrasena", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email, token, password })
        });

        const data = await response.json();
        if (response.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Contraseña actualizada',
                text: 'Redirigiendo al login...',
                timer: 2000,
                showConfirmButton: false
            });
            setTimeout(() => { window.location.href = "/login"; }, 2000);
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.error || 'No se pudo actualizar.' });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo conectar con el servidor.' });
    }
}
</script>
@endsection
