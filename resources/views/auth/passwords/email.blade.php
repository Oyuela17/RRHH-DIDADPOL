<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar Contraseña</title>
  @vite(['resources/css/login.css', 'resources/js/app.js'])
</head>
<body>

<div class="login-wrapper">
  <div class="card-login">

    <!-- Panel Izquierdo -->
    <div class="card-left">
      <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <h2>Recuperar contraseña</h2>

        @if (session('status'))
          <div class="form-group" style="color: #fff;">
            {{ session('status') }}
          </div>
        @endif

        <div class="form-group">
          <input type="email" name="email" placeholder="Correo electrónico" required autofocus />
          @error('email')
            <span class="text-danger">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-group">
          <button type="submit" class="btn-login">Enviar enlace</button>
        </div>

        <div class="form-group">
          <a class="forgot-password" href="{{ route('login') }}">
            ¿Recordaste tu contraseña? Inicia sesión
          </a>
        </div>
      </form>
    </div>

    <!-- Panel Derecho -->
  

    <div class="login-right">
    <img src="{{ asset('imagen/LOGO_OFICIAL.png') }}" alt="Logo DIDADPOL" class="logo-panel">
</div>

  </div>
</div>

</body>
</html>
