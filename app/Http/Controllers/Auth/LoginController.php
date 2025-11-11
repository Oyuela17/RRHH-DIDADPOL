<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    /**
     * Mostrar formulario de login personalizado.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Paso 1: Validar credenciales y lanzar 2FA (NO autentica aún).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email    = strtolower($credentials['email']);
        $password = $credentials['password'];
        $ip       = $request->ip();
        $ua       = $request->userAgent();

        // Buscar usuario
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user) {
            $this->registrarIntentoEnBD($email, false, $ip, $ua, 'USUARIO_NO_EXISTE');
            return back()->withInput()->with('error', 'Correo o contraseña incorrectos.');
        }

        // Estado
        if (strcasecmp($user->estado, 'INACTIVO') === 0) {
            $this->registrarIntentoEnBD($email, false, $ip, $ua, 'USUARIO_INACTIVO');
            return back()->withInput()->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        // Contraseña
        if (!Hash::check($password, $user->password)) {
            $res      = $this->registrarIntentoEnBD($email, false, $ip, $ua, 'PASSWORD_INCORRECTO');
            $status   = $res['status'] ?? null;
            $intentos = $res['intentos'] ?? null;

            if ($status === 'blocked') {
                return back()->withInput()->with('error', 'Usuario bloqueado por intentos fallidos. Contacta al administrador.');
            }

            $msg = 'Correo o contraseña incorrectos.';
            if (is_numeric($intentos)) {
                $msg = "Contraseña incorrecta. Intento {$intentos} de 3.";
            }
            return back()->withInput()->with('error', $msg);
        }

        // Rol
        $rol = DB::table('roles')
            ->join('role_user', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->select('roles.nombre', 'roles.estado')
            ->first();

        if (!$rol) {
            return back()->withInput()->with('error', 'Acceso denegado. No tienes un rol asignado.');
        }
        if (strcasecmp($rol->estado, 'ACTIVO') !== 0) {
            return back()->withInput()->with('error', 'Acceso denegado. Tu rol está inactivo.');
        }

        // ===== Iniciar 2FA con API Node (Render) =====
        try {
            $baseUrl    = 'https://rrhh-didadpol-1.onrender.com'; // URL fija de tu API Node
            $timeout    = 15;
            $adminToken = env('ADMIN_TOKEN');

            $http = Http::timeout($timeout);
            if (!empty($adminToken)) {
                $http = $http->withHeaders(['Authorization' => 'Bearer ' . $adminToken]);
            }

            $resp = $http->post($baseUrl . '/api/2fa/start', ['email' => $email]);

            if (!$resp->successful()) {
                return back()->with('error', 'No fue posible iniciar la verificación. Inténtalo de nuevo.');
            }

            $data = $resp->json();

            // Guardar info de 2FA en sesión
            session([
                '2fa.challenge_id' => $data['challenge_id'] ?? null,
                '2fa.email'        => $email,
                '2fa.user_id'      => $user->id,
                '2fa.remember'     => $request->filled('remember'),
                '2fa.rol_nombre'   => $rol->nombre,
                '2fa.expires_in'   => $data['expires_in'] ?? null,
                '2fa.cooldown'     => $data['cooldown_resend'] ?? null,
            ]);

            // Mostrar modal
            return back()
                ->with('pending_2fa', true)
                ->with('masked_email', $data['masked_email'] ?? null)
                ->with('expires_in', $data['expires_in'] ?? null)
                ->with('cooldown', $data['cooldown_resend'] ?? null);
        } catch (\Throwable $e) {
            \Log::error('2FA start error: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un problema iniciando la verificación.');
        }
    }

    /**
     * Paso 2: Verificar código 2FA (AJAX) y autenticar.
     * Responde JSON para el modal.
     */
    public function verify2fa(Request $request)
    {
        try {
            $code = trim((string)$request->input('code', ''));

            $challengeId = session('2fa.challenge_id');
            $email       = session('2fa.email');
            $userId      = session('2fa.user_id');
            $rolNombre   = session('2fa.rol_nombre');
            $remember    = (bool) session('2fa.remember');

            if (!$challengeId || !$userId || !$email) {
                return response()->json(['error' => 'Sesión de verificación no encontrada.'], 409);
            }
            if (strlen($code) !== 6) {
                return response()->json(['error' => 'Código inválido.'], 422);
            }

            $baseUrl    = 'https://rrhh-didadpol-1.onrender.com';
            $timeout    = 15;
            $adminToken = env('ADMIN_TOKEN');

            $http = Http::timeout($timeout);
            if (!empty($adminToken)) {
                $http = $http->withHeaders(['Authorization' => 'Bearer ' . $adminToken]);
            }

            $resp = $http->post($baseUrl . '/api/2fa/verify', [
                'challenge_id' => $challengeId,
                'code'         => $code,
            ]);

            $data = $resp->json();

            if (!$resp->successful() || empty($data['ok'])) {
                // Propaga mensaje del API si existe
                $msg = $data['error'] ?? 'Código inválido o expirado.';
                return response()->json(['error' => $msg], $resp->status() ?: 400);
            }

            // Éxito ⇒ autenticar definitivamente
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado.'], 404);
            }

            // Registrar intento OK en bitácora
            $this->registrarIntentoEnBD($email, true, $request->ip(), $request->userAgent(), null);

            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Guardar nombre del rol en sesión
            session(['nombre_rol' => $rolNombre]);

            // Limpiar llaves 2FA
            session()->forget([
                '2fa.challenge_id', '2fa.email', '2fa.user_id',
                '2fa.remember', '2fa.rol_nombre', '2fa.expires_in', '2fa.cooldown',
            ]);

            return response()->json([
                'ok'       => true,
                'redirect' => url('/home'),
            ]);
        } catch (\Throwable $e) {
            \Log::error('2FA verify error: ' . $e->getMessage());
            return response()->json(['error' => 'Error al verificar.'], 500);
        }
    }

    /**
     * Reenviar código 2FA (AJAX). Responde JSON.
     */
    public function resend2fa(Request $request)
    {
        try {
            $challengeId = session('2fa.challenge_id');
            if (!$challengeId) {
                return response()->json(['error' => 'Sesión de verificación no encontrada.'], 409);
            }

            $baseUrl    = 'https://rrhh-didadpol-1.onrender.com';
            $timeout    = 15;
            $adminToken = env('ADMIN_TOKEN');

            $http = Http::timeout($timeout);
            if (!empty($adminToken)) {
                $http = $http->withHeaders(['Authorization' => 'Bearer ' . $adminToken]);
            }

            $resp = $http->post($baseUrl . '/api/2fa/resend', [
                'challenge_id' => $challengeId,
            ]);

            $data = $resp->json();

            if (!$resp->successful()) {
                $msg = $data['error'] ?? 'No fue posible reenviar el código.';
                return response()->json(['error' => $msg], $resp->status() ?: 400);
            }

            // Actualiza cooldown si el API lo devuelve
            if (isset($data['cooldown_resend'])) {
                session(['2fa.cooldown' => (int) $data['cooldown_resend']]);
            }

            return response()->json([
                'mensaje'  => $data['mensaje'] ?? 'Código reenviado',
                'cooldown' => $data['cooldown_resend'] ?? null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('2FA resend error: ' . $e->getMessage());
            return response()->json(['error' => 'Error al reenviar código.'], 500);
        }
    }

    /**
     * Cerrar sesión.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Invoca la función SQL public.login_registrar_intento(...)
     * Devuelve el JSON decodificado o [] si algo falla.
     */
    private function registrarIntentoEnBD(string $email, bool $exito, string $ip, string $ua, ?string $motivo): array
    {
        try {
            $rows = DB::select(
                "SELECT public.login_registrar_intento(?, ?, ?, ?, ?) AS result",
                [$email, $exito, $ip, $ua, $motivo]
            );

            if (!empty($rows) && property_exists($rows[0], 'result')) {
                $decoded = json_decode($rows[0]->result, true);
                return is_array($decoded) ? $decoded : [];
            }
        } catch (\Throwable $e) {
            \Log::warning('login_registrar_intento error: ' . $e->getMessage());
        }

        return [];
    }
}
