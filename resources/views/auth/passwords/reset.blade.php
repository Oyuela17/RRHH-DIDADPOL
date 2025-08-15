<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar Contraseña</title>
  @vite(['resources/css/login.css', 'resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="login-wrapper">
  <div class="card-login">

    <!-- Panel Izquierdo -->
    <div class="card-left">
      <h2>Recuperar contraseña</h2>

      <form id="recuperarForm">
        <div class="form-group">
          <input type="email" name="email" id="email" placeholder="Correo electrónico" required />
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
    <div class="card-right">
      <div class="login-right">
        <img src="{{ asset('imagen/LOGO_OFICIAL.png') }}" alt="Logo DIDADPOL" class="logo-panel">
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('recuperarForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const email = document.getElementById('email').value;

    Swal.fire({
      title: 'Procesando...',
      html: 'Enviando enlace de recuperación <b>.</b><b>.</b><b>.</b>',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    try {
      const res = await fetch('http://localhost:3000/api/recuperar-contrasena', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
      });

      const data = await res.json();

      if (res.ok) {
        Swal.fire({
          icon: 'success',
          title: '¡Correo enviado!',
          text: 'Revise su correo para continuar y cierre esta pestaña.',
          confirmButtonText: 'OK'
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: data.error || 'Error al enviar el enlace'
        });
      }

    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor'
      });
    }
  });
</script>

</body>
</html>
