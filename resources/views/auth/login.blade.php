<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  @vite(['resources/css/login.css', 'resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    /* ===== Modal OTP (ligero, se adapta a tu tema) ===== */
    :root{
      --pri:#003366;     /* ajusta a tu paleta */
      --acc:#ff6b35;
      --ring: rgba(0,0,0,.1);
      --text:#1f2937;
      --muted:#64748b;
      --card-bg:#ffffff; /* tu login.css define fondo; mantenemos neutro */
    }
    .otp-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(6px);display:none;place-items:center;z-index:9999}
    .otp-modal{position:relative;width:100%;max-width:420px;background:var(--card-bg);color:var(--text);border:1px solid var(--ring);border-radius:18px;box-shadow:0 10px 40px rgba(0,0,0,.4);padding:22px 20px 18px;animation:otpPop .18s ease}
    @keyframes otpPop{from{transform:scale(.97);opacity:.6}to{transform:scale(1);opacity:1}}
    .otp-close{position:absolute;top:10px;right:10px;background:#f3f4f6;border:1px solid var(--ring);color:#111827;width:34px;height:34px;border-radius:10px;cursor:pointer}
    .otp-modal h3{margin:0 0 .25rem;font-weight:700;color:#0f172a}
    .otp-modal p{color:var(--muted);margin:.25rem 0 .75rem}
    .otp-inputs{display:flex;gap:.5rem;justify-content:center;margin:12px 0 6px}
    .otp-inputs input{width:48px;height:58px;text-align:center;font-size:22px;border-radius:14px;border:1px solid var(--ring);background:#f8fafc;color:#0f172a;outline:none}
    .otp-inputs input:focus{box-shadow:0 0 0 3px rgba(59,130,246,.25)}
    .otp-actions{display:flex;gap:.5rem;align-items:center;justify-content:space-between;margin-top:.5rem}
    #btnVerify{flex:1;padding:.85rem;border-radius:12px;border:none;background:linear-gradient(135deg,var(--acc),#f97316);color:#fff;cursor:pointer}
    .linklike{background:none;border:none;color:#2563eb;cursor:pointer}
    .linklike[disabled]{opacity:.5;cursor:not-allowed}
    .otp-msg{margin-top:.5rem;color:#dc2626;font-size:.95rem;min-height:1.2em}
  </style>
</head>
<body>

@if(request()->has('inactivo'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      Swal.fire({
        icon: 'info',
        title: 'Sesión cerrada',
        text: 'Tu sesión fue cerrada automáticamente por inactividad.',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#007bff'
      });
    });
  </script>
@endif

@if(session('error'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      Swal.fire({
        icon: 'error',
        title: 'Error de autenticación',
        text: '{{ session("error") }}',
        confirmButtonText: 'Intentar de nuevo',
        confirmButtonColor: '#dc3545'
      });
    });
  </script>
@endif

<div class="login-wrapper">
  <div class="card-login">

    <!-- IZQUIERDA -->
    <div class="card-left">
      <form id="formLogin" method="POST" action="{{ route('login') }}">
        @csrf
        <h2>Iniciar sesión</h2>

        <div class="form-group">
          <input type="email" name="email" id="email" placeholder="Correo electrónico" required autofocus>
        </div>

        <div class="form-group password-wrapper">
          <input type="password" name="password" id="password" placeholder="Contraseña" required>
          <span toggle="#password" class="toggle-password" onclick="togglePassword(this)">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </span>
        </div>

        <div class="form-group remember-me">
          <input type="checkbox" id="remember" name="remember">
          <label for="remember">Recordarme</label>
        </div>

        <div class="form-group">
          <button type="submit" id="btnLogin" class="btn-login">Ingresar</button>
        </div>

        @if (Route::has('password.request'))
        <div class="form-group">
          <a class="forgot-password" href="{{ route('password.request') }}">
            ¿Olvidaste tu contraseña?
          </a>
        </div>
        @endif

      </form>
    </div>

    <!-- DERECHA -->
    <div class="card-right">
      <div class="login-right">
        <img src="{{ asset('imagen/LOGO_OFICIAL.png') }}" alt="Logo DIDADPOL" class="logo-panel">
      </div>
    </div>

  </div>
</div>

<!-- ===== Modal OTP ===== -->
<div id="otpOverlay" class="otp-overlay" aria-hidden="true">
  <div class="otp-modal" role="dialog" aria-modal="true" aria-labelledby="otpTitle">
    <button class="otp-close" aria-label="Cerrar" onclick="cerrarOTP()">✕</button>
    <h3 id="otpTitle">Verificación en dos pasos</h3>
    <p>Enviamos un código de 6 dígitos a tu correo. Ingrésalo para continuar.</p>

    <div class="otp-inputs">
      <input maxlength="1" inputmode="numeric" autocomplete="one-time-code">
      <input maxlength="1" inputmode="numeric">
      <input maxlength="1" inputmode="numeric">
      <input maxlength="1" inputmode="numeric">
      <input maxlength="1" inputmode="numeric">
      <input maxlength="1" inputmode="numeric">
    </div>

    <div class="otp-actions">
      <button id="btnVerify" onclick="verificarOTP()">Verificar</button>
      <button id="btnResend" class="linklike" onclick="reenviarOTP()" disabled>
        Reenviar código (<span id="resendSec">60</span>s)
      </button>
    </div>

    <div id="otpMsg" class="otp-msg"></div>
  </div>
</div>

<script>
  // ===== Mostrar/Ocultar password =====
  function togglePassword(element) {
    const input = document.querySelector(element.getAttribute('toggle'));
    const isVisible = input.type === 'text';
    input.type = isVisible ? 'password' : 'text';
    element.innerHTML = isVisible
      ? `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#555" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>`
      : `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#555" stroke-width="2"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.4 21.4 0 0 1 5.29-6.71"/><path d="M1 1l22 22"/></svg>`;
  }

  // ===== 2FA por correo (sin tocar SQL) =====
  let tempSessionId = null;
  let resendCountdown = 60;
  let resendTimer = null;

  const formLogin = document.getElementById('formLogin');
  const btnLogin = document.getElementById('btnLogin');

  formLogin.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    btnLogin.disabled = true;

    try {
      const r = await fetch('/api/login-init', {
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ email, password })
      });
      const data = await r.json();
      if (!r.ok) throw new Error(data.error || 'Error al iniciar');

      tempSessionId = data.temp_session_id;
      mostrarOTP();

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Acceso denegado',
        text: err.message || 'No se pudo iniciar sesión',
        confirmButtonColor: '#dc3545'
      });
    } finally {
      btnLogin.disabled = false;
    }
  });

  function mostrarOTP(){
    const overlay = document.getElementById('otpOverlay');
    overlay.style.display = 'grid';
    overlay.setAttribute('aria-hidden', 'false');

    const inputs = document.querySelectorAll('.otp-inputs input');
    inputs.forEach((inp, idx) => {
      inp.value = '';
      inp.addEventListener('input', (ev)=>{
        ev.target.value = ev.target.value.replace(/\D/g,'').slice(0,1);
        if (ev.target.value && idx < inputs.length-1) inputs[idx+1].focus();
      });
      inp.addEventListener('keydown', (ev)=>{
        if (ev.key === 'Backspace' && !inp.value && idx>0) inputs[idx-1].focus();
        if (ev.key === 'Enter') verificarOTP();
      });
    });
    inputs[0].focus();

    // cooldown de reenvío
    iniciarCooldownReenvio(60);
  }

  function cerrarOTP(){
    const overlay = document.getElementById('otpOverlay');
    overlay.style.display = 'none';
    overlay.setAttribute('aria-hidden', 'true');
    clearInterval(resendTimer);
  }

  function codigoOTP(){
    return Array.from(document.querySelectorAll('.otp-inputs input'))
      .map(i=>i.value||'').join('');
  }

  async function verificarOTP(){
    const code = codigoOTP();
    const msg = document.getElementById('otpMsg');
    msg.textContent = '';

    if (code.length !== 6) { msg.textContent = 'Ingresa los 6 dígitos'; return; }

    try {
      const r = await fetch('/api/otp/verify', {
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ temp_session_id: tempSessionId, code })
      });
      const data = await r.json();
      if (!r.ok) throw new Error(data.error || 'Código inválido');

      // ✅ Si usas token del API:
      if (data.token) localStorage.setItem('auth_token', data.token);

      // Redirigir al dashboard Laravel
      window.location.href = "{{ route('dashboard') }}";
    } catch (e) {
      msg.textContent = e.message || 'No se pudo verificar el código';
    }
  }

  async function reenviarOTP(){
    const btn = document.getElementById('btnResend');
    const msg = document.getElementById('otpMsg');
    msg.textContent = '';
    btn.disabled = true;

    try {
      const r = await fetch('/api/otp/resend', {
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ temp_session_id: tempSessionId })
      });
      const data = await r.json();
      if (!r.ok) throw new Error(data.error || 'No se pudo reenviar el código');

      msg.textContent = 'Se envió un nuevo código. Revisa tu correo.';
      iniciarCooldownReenvio(60);
    } catch (e) {
      msg.textContent = e.message || 'No se pudo reenviar el código';
      btn.disabled = false;
    }
  }

  function iniciarCooldownReenvio(segundos){
    clearInterval(resendTimer);
    resendCountdown = segundos;
    const sec = document.getElementById('resendSec');
    const btn = document.getElementById('btnResend');
    btn.disabled = true;
    sec.textContent = resendCountdown;
    resendTimer = setInterval(()=>{
      resendCountdown--;
      sec.textContent = resendCountdown;
      if (resendCountdown <= 0){
        clearInterval(resendTimer);
        btn.disabled = false;
      }
    },1000);
  }
</script>

</body>
</html>
