<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Forzar 2FA desde el controlador (sin .env).
     * Si está en true, el POST /login tradicional se bloquea
     * y se debe usar el flujo: /api/login-init -> /api/otp/verify -> /login-final
     */
    private bool $force2fa = true;

    /**
     * Secreto compartido con tu API Node para firmar el ticket de finalización 2FA.
     * Debe ser exactamente el mismo valor en Node (APP_2FA_SHARED_SECRET).
     * SUGERENCIA: usa una clave larga/aleatoria en producción.
     */
    private string $twoFASecret = 'DIDADPOL';

    /** Mostrar formulario de login */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Login tradicional con bitácora y validaciones.
     * Si $force2fa = true, se bloquea para no puentear 2FA.
     */
    public function login(Request $request)
    {
        if ($this->force2fa) {
            return back()
                ->withInput()
                ->with('error', 'Debes iniciar sesión con verificación en dos pasos (revisa tu correo).');
        }

        // 1) Validación
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = strtolower($credentials['email']);
        $password = $credentials['password'];
        $ip = $request->ip();
        $ua = $request->userAgent();

        // 2) Buscar usuario
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user) {
            $this->registrarIntentoEnBD($email, false, $ip, $ua, 'USUARIO_NO_EXISTE');
            return back()->withInput()->with('error', 'Correo o contraseña incorrectos.');
        }

        // INACTIVO
        if (strcasecmp($user->estado, 'INACTIVO') === 0) {
            $this->registrarIntentoEnBD($email, false, $ip, $ua, 'USUARIO_INACTIVO');
            return back()->withInput()->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        // 3) Password
        if (!Hash::check($password, $user->password)) {
            $res = $this->registrarIntentoEnBD($email, false, $ip, $ua, 'PASSWORD_INCORRECTO');
            $status = $res['status'] ?? null;
            $intentos = $res['intentos'] ?? null;

            if ($status === 'blocked') {
                return back()->withInput()->with('error', 'Usuario bloqueado por intentos fallidos. Contacta al administrador.');
            }

            $msg = 'Correo o contraseña incorrectos.';
            if (is_numeric($intentos)) $msg = "Contraseña incorrecta. Intento {$intentos} de 3.";
            return back()->withInput()->with('error', $msg);
        }

        // 4) Rol
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

        // 5) Éxito tradicional
        $this->registrarIntentoEnBD($email, true, $request->ip(), $request->userAgent(), null);

        Auth::login($user, $request->filled('remember'));
        $request->session()->regenerate();
        session(['nombre_rol' => $rol->nombre]);

        return redirect()->intended('/home');
    }

    /** Cerrar sesión */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * FINALIZAR LOGIN 2FA
     * Recibe: user_id, nonce, ts (segundos), sig (HMAC SHA256), remember (opcional)
     * Firma esperada: HMAC_SHA256( user_id . "." . nonce . "." . ts , $twoFASecret )
     * Valida ventana de 120s para el ticket.
     */
    public function finalizarLogin2FA(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer'],
            'nonce'   => ['required', 'string', 'min:8'],
            'ts'      => ['required', 'integer'],
            'sig'     => ['required', 'string', 'min:32'],
            'remember'=> ['nullable', 'boolean'],
        ]);

        $userId  = (int) $request->input('user_id');
        $nonce   = $request->input('nonce');
        $ts      = (int) $request->input('ts');
        $sig     = $request->input('sig');
        $remember= (bool) $request->boolean('remember');

        // 1) Ventana de tiempo (±120s)
        $now = time();
        if (abs($now - $ts) > 120) {
            return $this->finishResponse($request, false, 'Token de verificación expirado. Intenta nuevamente.');
        }

        // 2) HMAC
        $base = "{$userId}.{$nonce}.{$ts}";
        $calc = hash_hmac('sha256', $base, $this->twoFASecret);
        if (!hash_equals($calc, $sig)) {
            return $this->finishResponse($request, false, 'Token de verificación inválido.');
        }

        // 3) Usuario y estado
        $user = User::find($userId);
        if (!$user) {
            return $this->finishResponse($request, false, 'Usuario no válido.');
        }
        if (strcasecmp($user->estado, 'INACTIVO') === 0) {
            return $this->finishResponse($request, false, 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        // 4) Rol activo
        $rol = DB::table('roles')
            ->join('role_user', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->select('roles.nombre', 'roles.estado')
            ->first();

        if (!$rol || strcasecmp($rol->estado, 'ACTIVO') !== 0) {
            return $this->finishResponse($request, false, 'Acceso denegado. Rol no asignado o inactivo.');
        }

        // 5) Bitácora + sesión Laravel
        $this->registrarIntentoEnBD($user->email, true, $request->ip(), $request->userAgent(), '2FA_OK');

        Auth::login($user, $remember);
        $request->session()->regenerate();
        session(['nombre_rol' => $rol->nombre]);

        return $this->finishResponse($request, true, null, '/home');
    }

    /**
     * Respuesta flexible: JSON si el request lo desea, o redirección web.
     */
    private function finishResponse(Request $request, bool $ok, ?string $error = null, string $redirectTo = '/home')
    {
        if ($request->wantsJson() || $request->expectsJson()) {
            if ($ok) return response()->json(['ok' => true, 'redirect' => $redirectTo]);
            return response()->json(['ok' => false, 'error' => $error], 422);
        }

        if ($ok) return redirect()->intended($redirectTo);
        return back()->with('error', $error ?? 'Error al finalizar 2FA.');
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
