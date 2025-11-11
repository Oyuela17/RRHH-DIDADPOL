<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  @vite(['resources/css/login.css', 'resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
  {{-- Mensaje de sesión cerrada por inactividad --}}
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

  {{-- Error de autenticación --}}
  @if(session('error'))
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
          icon: 'error',
          title: 'Error de autenticación',
          text: @json(session('error')),
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
        <form id="loginForm" method="POST" action="{{ route('login') }}">
          @csrf
          <h2>Iniciar sesión</h2>

          <div class="form-group">
            <input type="email" name="email" placeholder="Correo electrónico" required autofocus>
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
            <button type="submit" class="btn-login">Ingresar</button>
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

  {{-- ===== Modal 2FA con SweetAlert (glass + OTP 6 dígitos) ===== --}}
  @if (session('pending_2fa'))
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const maskedEmail = @json(session('masked_email'));
        let expiresIn = Number(@json(session('expires_in') ?? 300)); // s
        let cooldown  = Number(@json(session('cooldown') ?? 60));   // s
        const csrf    = document.querySelector('#loginForm input[name="_token"]').value;

        const otpTemplate = `
          <div class="otp-wrapper">
            <div class="otp-head">
              <img src="{{ asset('imagen/LOGO_OFICIAL.png') }}" alt="DIDADPOL" class="otp-logo"/>
              <div class="otp-title">Verificación en dos pasos</div>
              <div class="otp-sub">Hemos enviado un código a <strong>${maskedEmail ?? ''}</strong></div>
            </div>
            <div class="otp-inputs" role="group" aria-label="Código de verificación de 6 dígitos">
              ${Array.from({length:6}).map((_,i)=>`<input inputmode="numeric" maxlength="1" class="otp-box" aria-label="Dígito ${i+1}">`).join('')}
            </div>
            <div class="otp-meta">
              <span class="otp-expira">Expira en <span id="otp-countdown">--:--</span></span>
              <button id="otp-resend" type="button" class="btn-resend" disabled>Reenviar</button>
              <span id="otp-badge" class="resend-badge" hidden></span>
            </div>
          </div>
        `;

        let tExpira, tResend;

        function startCountdown() {
          const el = document.getElementById('otp-countdown');
          clearInterval(tExpira);
          tExpira = setInterval(()=>{
            if (expiresIn <= 0) {
              clearInterval(tExpira);
              el.textContent = '00:00';
              document.querySelector('.swal2-confirm').disabled = true;
              const r = document.getElementById('otp-resend'); if (r) r.disabled = true;
              return;
            }
            expiresIn--;
            const m = String(Math.floor(expiresIn/60)).padStart(2,'0');
            const s = String(expiresIn%60).padStart(2,'0');
            el.textContent = `${m}:${s}`;
          },1000);
        }

        function startResendCooldown() {
          const btn   = document.getElementById('otp-resend');
          const badge = document.getElementById('otp-badge');
          let left = cooldown;
          btn.disabled = true;
          badge.hidden = false;
          badge.textContent = `Reintentar en ${left}s`;
          clearInterval(tResend);
          tResend = setInterval(()=>{
            left--;
            if (left<=0) {
              clearInterval(tResend);
              btn.disabled = false;
              badge.hidden = true;
            } else {
              badge.textContent = `Reintentar en ${left}s`;
            }
          },1000);
        }

        function bindOTP() {
          const boxes = [...document.querySelectorAll('.otp-box')];
          boxes[0].focus();

          boxes.forEach((box, idx) => {
            box.addEventListener('input', e => {
              e.target.value = e.target.value.replace(/\D/g,'');
              if (e.target.value && idx < boxes.length - 1) boxes[idx+1].focus();
            });
            box.addEventListener('keydown', e => {
              if (e.key === 'Backspace' && !box.value && idx>0) boxes[idx-1].focus();
              if (e.key === 'ArrowLeft'  && idx>0) boxes[idx-1].focus();
              if (e.key === 'ArrowRight' && idx<boxes.length-1) boxes[idx+1].focus();
            });
          });

          boxes[0].addEventListener('paste', e => {
            const data = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
            if (data.length) {
              e.preventDefault();
              data.split('').forEach((d,i)=>{ if (boxes[i]) boxes[i].value = d; });
              (boxes[Math.min(data.length,5)]).focus();
            }
          });
        }

        async function resendCode() {
          const btn = document.getElementById('otp-resend');
          try {
            btn.disabled = true;
            const res = await fetch(@json(route('2fa.resend')), {
              method:'POST',
              headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
              body: JSON.stringify({})
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data?.error || 'No fue posible reenviar el código.');
            cooldown = Number(data?.cooldown ?? cooldown);
            startResendCooldown();
          } catch(err) {
            Swal.showValidationMessage(err.message);
            btn.disabled = false;
          }
        }

        function openModal() {
          Swal.fire({
            html: otpTemplate,
            showConfirmButton: true,
            confirmButtonText: 'Verificar',
            confirmButtonColor: '#0d6efd',
            customClass: { popup: 'otp-popup', confirmButton: 'btn-primary-solid' },
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
              bindOTP();
              startCountdown();
              startResendCooldown();
              document.getElementById('otp-resend').addEventListener('click', resendCode);
            },
            preConfirm: async () => {
              const code = [...document.querySelectorAll('.otp-box')].map(i=>i.value || '').join('');
              if (code.length !== 6) {
                Swal.showValidationMessage('Ingresa el código de 6 dígitos.');
                return false;
              }
              try {
                const res = await fetch(@json(route('2fa.verify')), {
                  method:'POST',
                  headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                  body: JSON.stringify({ code })
                });
                const data = await res.json();
                if (!res.ok) {
                  Swal.showValidationMessage(data?.error || 'Código inválido o expirado.');
                  return false;
                }
                window.location.assign(data?.redirect ?? @json(url('/home')));
                return true;
              } catch(e) {
                Swal.showValidationMessage('Error de red al verificar.');
                return false;
              }
            }
          }).then(()=> {
            clearInterval(tExpira);
            clearInterval(tResend);
          });
        }

        openModal();
      });
    </script>
  @endif

  <script>
    function togglePassword(element) {
      const input = document.querySelector(element.getAttribute('toggle'));
      const isVisible = input.type === 'text';

      input.type = isVisible ? 'password' : 'text';
      element.innerHTML = isVisible
        ? `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#555" stroke-width="2">
             <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
             <circle cx="12" cy="12" r="3"/>
           </svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#555" stroke-width="2">
             <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.4 21.4 0 0 1 5.29-6.71"/>
             <path d="M1 1l22 22"/>
           </svg>`;
    }
  </script>
</body>
</html>
