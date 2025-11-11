<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/css/login.css', 'resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root{
      --pri:#003366; --acc:#ff6b35; --ring:rgba(0,0,0,.1);
      --text:#0f172a; --muted:#64748b; --card:#fff;
    }
    .otp-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);display:none;place-items:center;z-index:9999}
    .otp-modal{position:relative;width:100%;max-width:420px;background:var(--card);border:1px solid var(--ring);border-radius:18px;box-shadow:0 10px 40px rgba(0,0,0,.35);padding:22px 20px}
    .otp-close{position:absolute;top:10px;right:10px;background:#f3f4f6;border:1px solid var(--ring);width:34px;height:34px;border-radius:10px;cursor:pointer}
    .otp-modal h3{margin:0 0 .25rem;font-weight:700;color:var(--text)}
    .otp-modal p{color:var(--muted);margin:.25rem 0 .75rem}
    .otp-inputs{display:flex;gap:.5rem;justify-content:center;margin:12px 0 6px}
    .otp-inputs input{width:48px;height:58px;text-align:center;font-size:22px;border-radius:14px;border:1px solid var(--ring);background:#f8fafc;outline:none}
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

<!-- Modal OTP -->
<div id="otpOverlay" class="otp-overlay" aria-hidden="true">
  <div class="otp-modal" role="dialog" aria-modal="true" aria-labelledby="otpTitle">
    <button class="otp-close" aria-label="Cerrar" onclick="cerrarOTP()">✕</button>
    <h3 id="otpTitle">Verificación en dos pasos</h3>
    <p>Te enviamos un código de verificación a tu correo. Ingrésalo para continuar.</p>

    <div class="otp-inputs" id="otpInputs">
      <input type="text" inputmode="numeric" maxlength="1">
      <input type="text" inputmode="numeric" maxlength="1">
      <input type="text" inputmode="numeric" maxlength="1">
      <input type="text" inputmode="numeric" maxlength="1">
      <input type="text" inputmode="numeric" maxlength="1">
      <input type="text" inputmode="numeric" maxlength="1">
    </div>

    <div class="otp-actions">
      <button id="btnVerify">Verificar</button>
      <button id="btnResend" class="linklike" type="button">Reenviar código</button>
    </div>
    <div class="otp-msg" id="otpMsg"></div>
  </div>
</div>

<!-- Scripts -->
<script>
  function togglePassword(element) {
    const input = document.querySelector(element.getAttribute('toggle'));
    const isVisible = input.type === 'text';
    input.type = isVisible ? 'password' : 'text';
    element.innerHTML = isVisible
      ? `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#555" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>`
      : `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#555" stroke-width="2"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.4 21.4 0 0 1 5.29-6.71"/><path d="M1 1l22 22"/></svg>`;
  }

  // ===== 2FA front-flow =====
  const form = document.getElementById('formLogin');
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const overlay = document.getElementById('otpOverlay');
  const inputsWrap = document.getElementById('otpInputs');
  const msg = document.getElementById('otpMsg');
  const btnVerify = document.getElementById('btnVerify');
  const btnResend = document.getElementById('btnResend');

  let tempSessionId = null;

  function abrirOTP() { overlay.style.display = 'grid'; overlay.setAttribute('aria-hidden', 'false'); setTimeout(()=>inputsWrap.querySelector('input')?.focus(), 0); }
  function cerrarOTP() { overlay.style.display = 'none'; overlay.setAttribute('aria-hidden', 'true'); inputsWrap.querySelectorAll('input').forEach(i=>i.value=''); msg.textContent=''; tempSessionId=null; }

  inputsWrap.addEventListener('input', (e) => {
    const t = e.target;
    if (t.tagName !== 'INPUT') return;
    t.value = t.value.replace(/\D/g,'').slice(0,1);
    if (t.value && t.nextElementSibling) t.nextElementSibling.focus();
  });
  inputsWrap.addEventListener('keydown', (e) => {
    if (e.key === 'Backspace' && e.target.value === '' && e.target.previousElementSibling) {
      e.target.previousElementSibling.focus();
    }
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault(); // no enviar a /login
    msg.textContent = '';

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const remember = document.getElementById('remember').checked;

    if (!email || !password) {
      Swal.fire({icon:'warning', title:'Campos requeridos', text:'Ingresa correo y contraseña.'});
      return;
    }

    try {
      const r = await fetch('/api/login-init', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ email, password })
      });
      const data = await r.json();
      if (!r.ok) {
        Swal.fire({icon:'error', title:'Error de autenticación', text: data.error || 'No se pudo iniciar.'});
        return;
      }
      tempSessionId = data.temp_session_id;
      abrirOTP();
    } catch (err) {
      Swal.fire({icon:'error', title:'Error', text:'No se pudo conectar con el servidor.'});
    }
  });

  btnVerify.addEventListener('click', async () => {
    if (!tempSessionId) return;
    const code = Array.from(inputsWrap.querySelectorAll('input')).map(i=>i.value).join('');
    if (code.length !== 6) { msg.textContent = 'Ingresa los 6 dígitos.'; return; }

    try {
      const r = await fetch('/api/otp/verify', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ temp_session_id: tempSessionId, code })
      });
      const data = await r.json();
      if (!r.ok || !data.ok) {
        msg.textContent = data.error || 'Código incorrecto.';
        return;
      }

      // Finalizar login en Laravel
      const remember = document.getElementById('remember').checked ? 1 : 0;
      const r2 = await fetch('{{ route('login.final') }}', {
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify({
          user_id: data.user_id,
          nonce: data.nonce,
          ts: data.ts,
          sig: data.sig,
          remember
        })
      });

      if (r2.headers.get('content-type')?.includes('application/json')) {
        const d2 = await r2.json();
        if (!d2.ok) { msg.textContent = d2.error || 'No se pudo completar el inicio.'; return; }
        window.location.href = d2.redirect || '/home';
      } else {
        // si el controlador redirige directamente
        window.location.href = '/home';
      }
    } catch (err) {
      msg.textContent = 'Error de red. Intenta otra vez.';
    }
  });

  btnResend.addEventListener('click', async () => {
    if (!tempSessionId) return;
    btnResend.disabled = true;
    try {
      const r = await fetch('/api/otp/resend', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ temp_session_id: tempSessionId })
      });
      const data = await r.json();
      if (!r.ok) {
        msg.textContent = data.error || 'No se pudo reenviar el código.';
      } else {
        msg.style.color = '#16a34a';
        msg.textContent = 'Código reenviado. Revisa tu correo.';
        setTimeout(()=>{ msg.style.color = '#dc2626'; }, 3000);
      }
    } catch {
      msg.textContent = 'Error de red.';
    } finally {
      // cooldown visual 60s
      let s = 60;
      const id = setInterval(()=>{
        btnResend.textContent = `Reenviar código (${--s}s)`;
        if (s<=0){ clearInterval(id); btnResend.textContent = 'Reenviar código'; btnResend.disabled=false; }
      },1000);
    }
  });

  function cerrarOTP(){ overlay.style.display='none'; overlay.setAttribute('aria-hidden','true'); }
</script>

</body>
</html>
