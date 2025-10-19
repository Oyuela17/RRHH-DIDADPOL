<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Definir Nueva Contraseña</title>
  @vite(['resources/css/login.css', 'resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="login-wrapper">
  <div class="card-login" id="cardLogin">
    <!-- IZQUIERDA -->
    <div class="card-left">
      <form id="formDefinir" onsubmit="definirContrasena(event)">
        <h2>Definir Nueva Contraseña</h2>
        <p class="subtitle">Crea una contraseña segura para tu cuenta</p>

        <input type="hidden" id="token" value="{{ request()->get('token') }}">
        <input type="hidden" id="email" value="{{ request()->get('email') }}">

        <!-- Campo contraseña -->
        <div class="form-group password-wrapper">
          <input type="password" id="password" placeholder="Nueva contraseña" required autocomplete="new-password">
          <span toggle="#password" class="toggle-password" onclick="togglePassword(this)">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </span>

          <!-- Tooltip arriba -->
          <ul class="requisitos-tooltip top" id="requisitos">
            <li id="req-length">✔ Mínimo 8 caracteres</li>
            <li id="req-uppercase">✔ Una letra mayúscula</li>
            <li id="req-lowercase">✔ Una letra minúscula</li>
            <li id="req-number">✔ Un número</li>
            <li id="req-symbol">✔ Un símbolo especial</li>
          </ul>

          <!-- Mensaje interno -->
          <span id="mensajeInline" class="mensaje-inline">Debe cumplir todos los requisitos de seguridad</span>
        </div>

        <!-- Barra moderna -->
        <div class="barra-seguridad">
          <div id="nivel-seguridad" class="nivel-seguridad"></div>
          <div class="glow"></div>
        </div>
        <div id="texto-seguridad" class="texto-seguridad"></div>

        <!-- Confirmar contraseña -->
        <div class="form-group password-wrapper">
          <input type="password" id="confirmar" placeholder="Confirmar contraseña" required autocomplete="new-password">
          <span toggle="#confirmar" class="toggle-password" onclick="togglePassword(this)">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </span>

          <!-- Mensaje interno de coincidencia -->
          <span id="mensajeCoincidencia" class="mensaje-inline">Las contraseñas no coinciden</span>
        </div>

        <button type="submit" id="btnGuardar" class="btn-login" disabled>Guardar Contraseña</button>
      </form>
    </div>

    <!-- DERECHA -->
    <div class="card-right">
      <img src="{{ asset('imagen/LOGO_OFICIAL.png') }}" alt="Logo DIDADPOL" class="logo-panel">
    </div>
  </div>
</div>

<script>
function togglePassword(element) {
  const input = document.querySelector(element.getAttribute('toggle'));
  input.type = input.type === 'text' ? 'password' : 'text';
}

const passwordInput = document.getElementById('password');
const confirmarInput = document.getElementById('confirmar');
const nivelSeguridad = document.getElementById('nivel-seguridad');
const textoSeguridad = document.getElementById('texto-seguridad');
const btnGuardar = document.getElementById('btnGuardar');
const requisitos = document.getElementById('requisitos');
const mensajeInline = document.getElementById('mensajeInline');
const mensajeCoincidencia = document.getElementById('mensajeCoincidencia');
const cardLogin = document.getElementById('cardLogin');

let hideTimeout;

function validarPassword() {
  const val = passwordInput.value;
  const confirmVal = confirmarInput.value;

  clearTimeout(hideTimeout);

  // Mostrar tooltip solo mientras escribes en "password"
  if (document.activeElement === passwordInput && val.length > 0) {
    requisitos.classList.add('visible');
  } else {
    requisitos.classList.remove('visible');
  }

  const reqs = [
    [val.length >= 8, 'req-length'],
    [/[A-Z]/.test(val), 'req-uppercase'],
    [/[a-z]/.test(val), 'req-lowercase'],
    [/\d/.test(val), 'req-number'],
    [/[^A-Za-z0-9]/.test(val), 'req-symbol']
  ];

  let strength = reqs.reduce((acc, [ok]) => ok ? acc + 1 : acc, 0);
  reqs.forEach(([ok, id]) => {
    const li = document.getElementById(id);
    if (li) li.style.color = ok ? '#22c55e' : '#9ca3af';
  });

  let color, label, width;
  if (strength <= 2) { color='#ff4d4d'; label='Débil'; width='25%'; }
  else if (strength <= 4) { color='#ffb700'; label='Media'; width='70%'; }
  else { color='#22c55e'; label='Fuerte'; width='100%'; }

  nivelSeguridad.style.background = `linear-gradient(90deg, ${color}, #ffffff22)`;
  nivelSeguridad.style.width = width;
  textoSeguridad.textContent = `Seguridad: ${label}`;
  textoSeguridad.style.color = color;

  // Mensaje inline de requisitos
  mensajeInline.style.opacity = (val && strength < 5) ? 1 : 0;

  // Ocultar requisitos si completa o cambia de input
  hideTimeout = setTimeout(() => {
    if (strength === 5 || val.length === 0) requisitos.classList.remove('visible');
  }, 1000);

  // Mensaje inline de coincidencia (en confirmar)
  mensajeCoincidencia.style.opacity = (confirmVal && val !== confirmVal) ? 1 : 0;

  btnGuardar.disabled = !(strength === 5 && val === confirmVal);
}

passwordInput.addEventListener('input', validarPassword);
confirmarInput.addEventListener('input', validarPassword);
confirmarInput.addEventListener('focus', () => requisitos.classList.remove('visible'));
passwordInput.addEventListener('blur', () => requisitos.classList.remove('visible'));

/* 🔔 salida suave: anima y luego cierra/redirige */
function smoothExit() {
  // añade clase de cierre
  cardLogin.classList.add('closing');
  // tras la animación, intenta cerrar y si no, redirige
  setTimeout(() => {
    window.close();                 // si fue ventana abierta por script, esto la cierra
    window.location.replace('/login'); // fallback
  }, 420); // debe coincidir con la duración del @keyframes cardOut
}

// ✅ Envío del formulario (con cierre suave o redirect)
async function definirContrasena(event) {
  event.preventDefault();

  const tokenEl = document.getElementById('token');
  const emailEl = document.getElementById('email');

  // Tomar primero los hidden; si vienen vacíos, leer de la URL
  const urlParams = new URLSearchParams(window.location.search);
  const token = (tokenEl && tokenEl.value) || urlParams.get('token');
  const email = (emailEl && emailEl.value) || urlParams.get('email');

  const password = passwordInput.value;

  if (!token || !email) {
    return Swal.fire({
      icon: 'error',
      title: 'Enlace inválido',
      text: 'Faltan datos en el enlace (token o correo).'
    });
  }

  btnGuardar.disabled = true;

  try {
    const res = await fetch('https://rrhh-didadpol-1.onrender.com/api/definir-contrasena', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token, email, password })
    });

    const data = await res.json();

    if (!res.ok) {
      const msg = data?.error || data?.message || 'No se pudo guardar la contraseña.';
      let titulo = 'Error';
      if (/token/i.test(msg)) titulo = 'Token inválido o expirado';
      if (/correo|email/i.test(msg)) titulo = 'Correo no registrado';

      await Swal.fire({ icon: 'error', title: titulo, text: msg });
      btnGuardar.disabled = false;
      return;
    }

    // Éxito: mostramos snackbar/alerta y luego salida suave
    await Swal.fire({
      icon: 'success',
      title: '¡Contraseña actualizada!',
      text: 'Tu contraseña ha sido guardada correctamente.',
      timer: 1400,
      showConfirmButton: false
    });

    // animación + cierre/redirect
    smoothExit();

  } catch (err) {
    console.error(err);
    await Swal.fire({
      icon: 'error',
      title: 'Error de conexión',
      text: 'No se pudo conectar con el servidor.'
    });
    btnGuardar.disabled = false;
  }
}
</script>

<style>
/* Fondo general */
body, html {
  height: 100%;
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #005796, #8d99ae);
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}

/* Contenedor */
.card-login {
  display: flex;
  width: 800px;
  height: 520px;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 20px;
  box-shadow: 0 15px 30px rgba(0,0,0,0.25);
  overflow: hidden;
  transform-origin: center;
  animation: cardEntrance 0.8s cubic-bezier(.2,.8,.2,1) both;
}
@keyframes cardEntrance {
  from { transform: translateY(10px) scale(.98); opacity: 0; filter: blur(2px); }
  to   { transform: translateY(0)    scale(1);    opacity: 1; filter: blur(0);  }
}

/* 👋 CIERRE SUAVE */
.card-login.closing {
  animation: cardOut 0.42s cubic-bezier(.2,.8,.2,1) forwards;
}
@keyframes cardOut {
  to { transform: translateY(10px) scale(.98); opacity: 0; filter: blur(3px); }
}

.card-left {
  flex: 1;
  background: linear-gradient(135deg, #004b84, #006ab3);
  color: #fff;
  padding: 40px 30px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.password-wrapper { position: relative; }

/* Inputs */
.password-wrapper input {
  width: 100%;
  padding: 12px 14px;
  font-size: 15px;
  border: none;
  border-radius: 8px;
  background: #ffffff;
  color: #333;
  transition: all 0.3s;
}
.password-wrapper input:focus {
  outline: none;
  box-shadow: 0 0 10px rgba(244, 163, 0, 0.5);
}

/* Icono ojo */
.toggle-password {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  cursor: pointer;
}

/* Tooltip (arriba del campo contraseña) */
.requisitos-tooltip {
  position: absolute;
  bottom: 110%;
  left: 0;
  background: rgba(255,255,255,0.97);
  color: #333;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.25);
  padding: 10px 14px;
  font-size: 13px;
  width: 100%;
  opacity: 0;
  transform: translateY(-10px);
  pointer-events: none;
  transition: all 0.4s ease;
}
.requisitos-tooltip.visible { opacity: 1; transform: translateY(0); }
.requisitos-tooltip li { margin-bottom: 4px; transition: color 0.3s ease; }

/* Mensajes inline (requisitos / coincidencia) */
.mensaje-inline {
  position: absolute;
  bottom: 6px;
  left: 14px;
  font-size: 12px;
  color: rgba(239,68,68,0.9);
  opacity: 0;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

/* Barra de seguridad moderna */
.barra-seguridad {
  position: relative;
  height: 10px;
  background: #d9d9d9;
  border-radius: 999px;
  overflow: hidden;
  margin-top: 20px;
}
.nivel-seguridad {
  height: 100%;
  width: 0%;
  border-radius: 999px;
  background: linear-gradient(90deg, #ff4d4d, #ffb700, #22c55e);
  transition: width 0.5s ease-in-out, background 0.5s ease;
}
.barra-seguridad .glow {
  position: absolute;
  top: 0; left: 0;
  height: 100%;
  width: 120%;
  background: radial-gradient(circle at 30% 50%, rgba(255,255,255,0.4), transparent 70%);
  animation: glowMove 2s infinite linear;
}
@keyframes glowMove {
  from { transform: translateX(-50%); }
  to   { transform: translateX(50%); }
}

/* Texto seguridad */
.texto-seguridad {
  margin-top: 6px;
  font-size: 13px;
  font-weight: bold;
  color: #fff;
}

/* Botón */
.btn-login {
  width: 100%;
  padding: 15px;
  background: linear-gradient(135deg, #f4a300, #f1bb4f);
  border: none;
  color: white;
  font-weight: bold;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 25px;
  transition: all 0.4s ease;
}
.btn-login:hover { transform: scale(1.05); }

/* Lado derecho */
.card-right {
  flex: 1;
  background: #ffffff;
  display: flex;
  justify-content: center;
  align-items: center;
}
.logo-panel { max-width: 300px; height: auto; }

/* Responsive opcional */
@media (max-width: 768px) {
  .card-login { width: 92%; height: auto; flex-direction: column; }
  .card-right { padding: 24px; }
}
</style>

</body>
</html>
