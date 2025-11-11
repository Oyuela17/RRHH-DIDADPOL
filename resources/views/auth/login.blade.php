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

  {{-- ===== Modal 2FA con SweetAlert (se dispara cuando pending_2fa está en sesión) ===== --}}
  @if (session('pending_2fa'))
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const maskedEmail = @json(session('masked_email'));
        let expiresIn = Number(@json(session('expires_in') ?? 300)); // segundos
        let cooldown = Number(@json(session('cooldown') ?? 60));     // segundos
        let resendDisabled = false;
        let countdownInterval = null;

        const csrf = document.querySelector('#loginForm input[name="_token"]').value;

        function startCountdown(container) {
          const el = container.querySelector('#didadpol-countdown');
          clearInterval(countdownInterval);
          countdownInterval = setInterval(() => {
            if (expiresIn <= 0) {
              clearInterval(countdownInterval);
              el.textContent = 'Expirado';
              container.querySelector('#btn-verify').disabled = true;
              container.querySelector('#btn-resend').disabled = true;
              return;
            }
            expiresIn--;
            const m = Math.floor(expiresIn / 60).toString().padStart(2,'0');
            const s = (expiresIn % 60).toString().padStart(2,'0');
            el.textContent = `${m}:${s}`;
          }, 1000);
        }

        function startResendCooldown(btn, badge) {
          resendDisabled = true;
          let left = cooldown;
          btn.disabled = true;
          badge.style.display = 'inline-block';
          badge.textContent = `Reintentar en ${left}s`;
          const t = setInterval(() => {
            left--;
            if (left <= 0) {
              clearInterval(t);
              resendDisabled = false;
              btn.disabled = false;
              badge.style.display = 'none';
            } else {
              badge.textContent = `Reintentar en ${left}s`;
            }
          }, 1000);
        }

        function open2FAModal() {
          Swal.fire({
            title: 'Verificación en dos pasos',
            html: `
              <div style="text-align:left">
                <p>Hemos enviado un código a <strong>${maskedEmail ?? ''}</strong>.</p>
                <label for="code" style="display:block;margin:8px 0 4px;">Ingresa el código</label>
                <input id="code" class="swal2-input" placeholder="••••••" maxlength="6" inputmode="numeric" style="text-align:center;letter-spacing:6px;">
                <div style="margin-top:6px;font-size:12px;color:#666">
                  Expira en: <span id="didadpol-countdown">--:--</span>
                </div>
                <div style="margin-top:14px; display:flex; gap:8px; align-items:center;">
                  <button id="btn-resend" class="swal2-styled" style="background:#6c757d;">Reenviar código</button>
                  <span id="resend-badge" style="display:none; font-size:12px; color:#444;"></span>
                </div>
              </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'Verificar',
            confirmButtonColor: '#0d6efd',
            customClass: { confirmButton: 'btn-verify' },
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
              const container = Swal.getHtmlContainer();
              const input = container.querySelector('#code');
              const btnResend = container.querySelector('#btn-resend');
              const badge = container.querySelector('#resend-badge');

              // focus y countdown
              input.focus();
              startCountdown(container);

              // reenvío
              btnResend.addEventListener('click', async () => {
                if (resendDisabled) return;
                try {
                  btnResend.disabled = true;
                  const res = await fetch(@json(route('2fa.resend')), {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({})
                  });
                  const data = await res.json();
                  if (!res.ok) {
                    Swal.showValidationMessage(data?.error || 'No fue posible reenviar el código.');
                    btnResend.disabled = false;
                    return;
                  }
                  cooldown = Number(data?.cooldown ?? cooldown);
                  startResendCooldown(btnResend, badge);
                  Swal.update({}); // para forzar reflow si hace falta
                } catch (e) {
                  btnResend.disabled = false;
                  Swal.showValidationMessage('Error de red al reenviar.');
                }
              });
            },
            preConfirm: async () => {
              const container = Swal.getHtmlContainer();
              const code = container.querySelector('#code')?.value?.trim();
              if (!code || code.length !== 6) {
                Swal.showValidationMessage('Ingresa el código de 6 dígitos.');
                return false;
              }
              try {
                const res = await fetch(@json(route('2fa.verify')), {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                  },
                  body: JSON.stringify({ code })
                });
                const data = await res.json();
                if (!res.ok) {
                  Swal.showValidationMessage(data?.error || 'Código inválido o expirado.');
                  return false;
                }
                // éxito -> redirigir
                window.location.assign(data?.redirect ?? @json(url('/home')));
                return true;
              } catch (e) {
                Swal.showValidationMessage('Error de red al verificar.');
                return false;
              }
            }
          }).then(() => {
            clearInterval(countdownInterval);
          });
        }

        // Abrir modal inmediatamente
        open2FAModal();
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
