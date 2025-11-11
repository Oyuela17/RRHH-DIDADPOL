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
      <form method="POST" action="{{ route('login') }}">
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

<script>
  function togglePassword(element) {
    const input = document.querySelector(element.getAttribute('toggle'));
    const isVisible = input.type === 'text';
    input.type = isVisible ? 'password' : 'text';
    element.innerHTML = isVisible
      ? `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#555" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>`
      : `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#555" stroke-width="2"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.4 21.4 0 0 1 5.29-6.71"/><path d="M1 1l22 22"/></svg>`;
  }

  @if (session('error'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({
      icon: 'error',
      title: 'Acceso denegado',
      text: '{{ session("error") }}',
      confirmButtonColor: '#007bff'
    });
  });
</script>
@endif

</script>

</body>
</html>
