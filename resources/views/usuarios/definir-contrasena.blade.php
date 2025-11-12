<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Definir Nueva Contraseña</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="page-bg">
  <div class="reset-card" id="cardLogin">
    <div class="card-watermark"></div>

    <div class="reset-header">
      <img src="{{ asset('imagen/LOGO_OFICIAL.png') }}" alt="Logo DIDADPOL" class="reset-logo">
      <h1>Definir Nueva Contraseña</h1>
      <p class="subtitle">Crea una contraseña segura para tu cuenta</p>
    </div>

    <form id="formDefinir" onsubmit="definirContrasena(event)" class="reset-form">
      <input type="hidden" id="token" value="{{ request()->get('token') }}">
      <input type="hidden" id="email" value="{{ request()->get('email') }}">

      <!-- Nueva contraseña -->
      <div class="form-group password-wrapper">
        <input type="password" id="password" placeholder="Nueva contraseña" required autocomplete="new-password">
        <button type="button" toggle="#password" class="toggle-password" onclick="togglePassword(this)" aria-label="Mostrar/Ocultar contraseña" title="Mostrar/Ocultar">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" width="20" height="20">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
        </button>

        <!-- Tooltip requisitos -->
        <ul class="requisitos-tooltip" id="requisitos" role="list">
          <li id="req-length">✔ Mínimo 8 caracteres</li>
          <li id="req-uppercase">✔ Una letra mayúscula</li>
          <li id="req-lowercase">✔ Una letra minúscula</li>
          <li id="req-number">✔ Un número</li>
          <li id="req-symbol">✔ Un símbolo especial</li>
        </ul>

        <span id="mensajeInline" class="mensaje-inline">Debe cumplir todos los requisitos de seguridad</span>
      </div>

      <!-- Barra seguridad -->
      <div class="barra-seguridad">
        <div id="nivel-seguridad" class="nivel-seguridad"></div>
      </div>
      <div id="texto-seguridad" class="texto-seguridad">Seguridad: —</div>

      <!-- Confirmar -->
      <div class="form-group password-wrapper">
        <input type="password" id="confirmar" placeholder="Confirmar contraseña" required autocomplete="new-password">
        <button type="button" toggle="#confirmar" class="toggle-password" onclick="togglePassword(this)" aria-label="Mostrar/Ocultar confirmación" title="Mostrar/Ocultar">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" width="20" height="20">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
            <circle cx="12" cy="12" r="3"/>
          </svg>
        </button>
        <span id="mensajeCoincidencia" class="mensaje-inline">Las contraseñas no coinciden</span>
      </div>

      <button type="submit" id="btnGuardar" class="btn-primary" disabled>Guardar Contraseña</button>
      <p class="hint">Por seguridad este enlace puede expirar. Si caduca, solicita uno nuevo.</p>
    </form>
  </div>
</div>

<script>
function togglePassword(btn) {
  const input = document.querySelector(btn.getAttribute('toggle'));
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
    if (li) li.style.color = ok ? '#16a34a' : '#9ca3af';
  });

  // Débil=rojo, Media=dorado, Fuerte=azul institucional
  let barColor, label, width;
  if (strength <= 2) { barColor='#ef4444'; label='Débil'; width='28%'; }
  else if (strength <= 4) { barColor='#f4a300'; label='Media'; width='70%'; }
  else { barColor='#005796'; label='Fuerte'; width='100%'; }

  nivelSeguridad.style.setProperty('--bar', barColor);
  nivelSeguridad.style.width = width;
  textoSeguridad.textContent = `Seguridad: ${label}`;
  textoSeguridad.style.color = barColor;

  mensajeInline.style.opacity = (val && strength < 5) ? 1 : 0;

  hideTimeout = setTimeout(() => {
    if (strength === 5 || val.length === 0) requisitos.classList.remove('visible');
  }, 900);

  mensajeCoincidencia.style.opacity = (confirmVal && val !== confirmVal) ? 1 : 0;

  btnGuardar.disabled = !(strength === 5 && val === confirmVal);
}

passwordInput.addEventListener('input', validarPassword);
confirmarInput.addEventListener('input', validarPassword);
confirmarInput.addEventListener('focus', () => requisitos.classList.remove('visible'));
passwordInput.addEventListener('blur', () => requisitos.classList.remove('visible'));

function smoothExit() {
  cardLogin.classList.add('closing');
  setTimeout(() => {
    window.close();
    window.location.replace('/login');
  }, 420);
}

async function definirContrasena(event) {
  event.preventDefault();

  const tokenEl = document.getElementById('token');
  const emailEl = document.getElementById('email');

  const urlParams = new URLSearchParams(window.location.search);
  const token = (tokenEl && tokenEl.value) || urlParams.get('token');
  const email = (emailEl && emailEl.value) || urlParams.get('email');
  const password = passwordInput.value;

  if (!token || !email) {
    return Swal.fire({ icon: 'error', title: 'Enlace inválido', text: 'Faltan datos en el enlace (token o correo).' });
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

    await Swal.fire({
      icon: 'success',
      title: '¡Contraseña actualizada!',
      text: 'Tu contraseña ha sido guardada correctamente.',
      timer: 1400,
      showConfirmButton: false
    });

    smoothExit();

  } catch (err) {
    console.error(err);
    await Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.' });
    btnGuardar.disabled = false;
  }
}
</script>

<style>
/* ===== Fondo institucional (igual al login) ===== */
html, body, .page-bg { height: 100%; }
body {
  margin: 0;
  font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  background: linear-gradient(135deg, #005796 0%, #7faed0 100%);
  background-repeat: no-repeat;
  background-size: cover;
  background-attachment: fixed;
}
.page-bg {
  position: relative;
  display:flex;
  align-items:center;
  justify-content:center;
  overflow:hidden;
  padding: 20px;
}

/* ===== Tarjeta ===== */
.reset-card {
  position: relative;
  width: 100%;
  max-width: 520px;
  padding: 24px 22px 20px;
  border-radius: 18px;
  background: rgba(255,255,255,0.92);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(15, 23, 42, 0.06);
  box-shadow:
    0 12px 24px rgba(2, 6, 23, .16),
    0 4px 10px rgba(2, 6, 23, .08);
  animation: cardEntrance .7s cubic-bezier(.2,.8,.2,1) both;
  overflow: hidden;                 /* 👈 nada se sale del modal */
  box-sizing: border-box;           /* 👈 mide con padding */
}
@keyframes cardEntrance {
  from { transform: translateY(8px); opacity: 0; }
  to   { transform: translateY(0);   opacity: 1; }
}
.reset-card.closing { animation: cardOut .42s cubic-bezier(.2,.8,.2,1) forwards; }
@keyframes cardOut { to { transform: translateY(6px); opacity: 0; } }

/* Marca de agua interna */
.card-watermark{
  position:absolute; inset:0; pointer-events:none;
  background: url("{{ asset('imagen/LOGO_OFICIAL.png') }}") no-repeat center 26%;
  background-size: 220px auto;
  opacity:.05;
}

/* ===== Header ===== */
.reset-header { position: relative; text-align:center; }
.reset-logo { height: 58px; width:auto; margin-bottom: 10px; }
.reset-header h1 { margin:0; font-size: 1.45rem; color:#0f172a; letter-spacing:.2px; }
.subtitle { margin:6px 0 0; font-size:.95rem; color:#475569; }

/* ===== Form ===== */
.reset-form {
  position: relative;
  margin-top: 16px;
  padding: 0 6px;                   /* 👈 acolcha para que nada roce el borde */
  box-sizing: border-box;
}
.form-group { margin-top: 14px; }
.password-wrapper { position: relative; }

/* Inputs */
.password-wrapper input{
  width: 100%;
  padding: 12px 44px 12px 14px;
  font-size: 15px;
  border: 1px solid #dbe3ee;
  border-radius: 12px;
  background: #ffffff;
  color: #0f172a;
  transition: box-shadow .25s, border-color .25s, background .25s;
  box-sizing: border-box;           /* 👈 evita desbordes */
}
.password-wrapper input::placeholder{ color:#9aa8bb; }
.password-wrapper input:focus{
  outline: none;
  border-color: #0c66a1;
  box-shadow: 0 0 0 4px rgba(12,102,161,.12);
}

/* Ojo */
.toggle-password{
  position:absolute; top:50%; right:12px; transform:translateY(-50%);
  cursor:pointer; display:inline-flex; align-items:center; justify-content:center;
  background: transparent; border:0; padding:0;
}

/* Tooltip requisitos */
.requisitos-tooltip{
  position:absolute; top:-8px; left:0; transform:translateY(-100%);
  width:100%;
  background: #ffffff; color:#0f172a;
  border:1px solid #e2e8f0; border-radius:12px;
  box-shadow: 0 12px 24px rgba(2,6,23,.08);
  padding:10px 14px; font-size:13px;
  opacity:0; pointer-events:none; transition: opacity .25s, transform .25s;
  box-sizing: border-box;
}
.requisitos-tooltip.visible{ opacity:1; transform:translateY(calc(-100% - 6px)); }
.requisitos-tooltip li{ margin-bottom:4px; }

/* Mensajes inline */
.mensaje-inline{
  position:absolute; right:12px; bottom:-18px;
  font-size:12px; color: rgba(239,68,68,.95);
  opacity:0; transition:opacity .2s; pointer-events:none;
}

/* ===== Barra de seguridad ===== */
.barra-seguridad{
  position: relative;
  height: 8px;
  background: #eaf0f7;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  overflow: hidden;
  margin-top: 16px;
  box-shadow: inset 0 0 4px rgba(2,6,23,.04);
  width: 100%;
  box-sizing: border-box;           /* 👈 se ajusta a padding del form */
}
.nivel-seguridad{
  --bar: #ef4444;
  height:100%;
  width:0%;
  border-radius:inherit;
  background: linear-gradient(90deg, var(--bar), rgba(255,255,255,.25));
  transition: width .45s ease, background .3s ease;
}
.texto-seguridad{
  margin-top: 6px;
  font-size:13px;
  font-weight:600;
  color:#334155;
}

/* Botón */
.btn-primary{
  width:100%;
  padding:14px;
  border:none;
  border-radius:12px;
  margin-top: 18px;
  background: linear-gradient(135deg, #f4a300, #f1bb4f);
  color:#fff; font-weight:600; letter-spacing:.2px; cursor:pointer;
  transition: transform .15s ease, opacity .2s ease, box-shadow .2s ease;
  box-shadow: 0 6px 18px rgba(244,163,0,.25);
  box-sizing: border-box;
}
.btn-primary:hover{ transform: translateY(-1px); }
.btn-primary:disabled{ opacity:.6; cursor:not-allowed; box-shadow:none; }

/* Nota */
.hint{ margin:10px 0 0; text-align:center; font-size:11px; color:#e6edf7; }

/* Responsive */
@media (max-width: 440px){
  .reset-card{ padding:20px 16px 16px; border-radius:16px; }
  .reset-form{ padding: 0 4px; }
  .reset-logo{ height:52px; }
  .card-watermark{ background-size: 170px auto; }
}
</style>

</body>
</html>
