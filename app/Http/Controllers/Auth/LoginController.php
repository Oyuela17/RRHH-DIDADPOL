<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Mostrar formulario de login personalizado
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Autenticar usuario con validaciones y registro en bitácora (BD)
     * NOTA: si activas 2FA forzado, este método devuelve error para
     * evitar que se "puentee" el 2FA por correo.
     */
    public function login(Request $request)
    {
        // --- OPCIONAL: Bloquear login tradicional cuando 2FA está activo ---
        if (config('app.force_2fa_login', env('APP_FORCE_2FA_LOGIN', false))) {
            return back()->withInput()->with('error', 'Debes iniciar sesión usando el 2FA (código enviado al correo).');
        }
        // -------------------------------------------------------------------

        // 1) Validar campos requeridos
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = strtolower($credentials['email']);
        $password = $credentials['password'];
        $ip = $request->ip();
        $ua = $request->userAgent();

        // 2) Buscar usuario (case-insensitive)
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        // 2.1) Usuario no existe
        if (!$user) {
            $this->registrarIntentoEnBD($email, false, $ip, $ua, 'USUARIO_NO_EXISTE');
            return back()->withInput()->with('error', 'Correo o contraseña incorrectos.');
        }

        // 2.2) Usuario INACTIVO
        if (strcasecmp($user->estado, 'INACTIVO') === 0) {
            $this->registrarIntentoEnBD($email, false, $ip, $ua, 'USUARIO_INACTIVO');
            return back()->withInput()->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        // 3) Verificar contraseña
        if (!Hash::check($password, $user->password)) {
            $res = $this->registrarIntentoEnBD($email, false, $ip, $ua, 'PASSWORD_INCORRECTO');
            $status = $res['status'] ?? null;
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

        // 4) Verificar rol
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

        // 5) Éxito tradicional (solo si NO tienes 2FA forzado)
        $this->registrarIntentoEnBD($email, true, $ip, $ua, null);

        Auth::login($user, $request->filled('remember'));
        $request->session()->regenerate();
        session(['nombre_rol' => $rol->nombre]);

        return redirect()->intended('/home');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Nuevo: Finalizar login después de OTP (2FA) validado en el backend Node.
     * Espera: user_id, nonce, ts (timestamp en segundos) y sig (HMAC).
     * La firma se calcula: HMAC_SHA256(user_id.nonce.ts, APP_2FA_SHARED_SECRET)
     */
    public function finalizarLogin2FA(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer'],
            'nonce'   => ['required', 'string', 'min:8'],
            'ts'      => ['required', 'integer'], // unix ts (segundos)
            'sig'     => ['required', 'string', 'min:32'],
            'remember'=> ['nullable', 'boolean'],
        ]);

        $userId = (int) $request->input('user_id');
        $nonce  = $request->input('nonce');
        $ts     = (int) $request->input('ts');
        $sig    = $request->input('sig');
        $remember = (bool) $request->boolean('remember');

        // 1) Ventana de tiempo para el ticket de finalización (ej. 120s)
        $now = time();
        if (abs($now - $ts) > 120) {
            return back()->with('error', 'Token de verificación expirado. Intenta nuevamente.');
        }

        // 2) Recalcular HMAC con secreto compartido
        $secret = env('APP_2FA_SHARED_SECRET');
        if (!$secret) {
            return back()->with('error', 'Falta configuración de 2FA en el servidor.');
        }

        $base = "{$userId}.{$nonce}.{$ts}";
        $calc = hash_hmac('sha256', $base, $secret);

        // Comparación timing-safe
        if (!hash_equals($calc, $sig)) {
            return back()->with('error', 'Token de verificación inválido.');
        }

        // 3) Cargar usuario y verificar que esté activo y con rol
        $user = User::find($userId);
        if (!$user) return back()->with('error', 'Usuario no válido.');
        if (strcasecmp($user->estado, 'INACTIVO') === 0) {
            return back()->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        $rol = DB::table('roles')
            ->join('role_user', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->select('roles.nombre', 'roles.estado')
            ->first();

        if (!$rol || strcasecmp($rol->estado, 'ACTIVO') !== 0) {
            return back()->with('error', 'Acceso denegado. Rol no asignado o inactivo.');
        }

        // 4) Autenticar en Laravel
        Auth::login($user, $remember);
        $request->session()->regenerate();
        session(['nombre_rol' => $rol->nombre]);

        return redirect()->intended('/home');
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
            // \Log::warning('login_registrar_intento error: '.$e->getMessage());
        }
        return [];
    }
}
