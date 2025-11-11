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
     * Forzar 2FA desde el controlador (sin .env)
     * Si está en true, el POST /login tradicional se bloquea
     * y debes usar el flujo /api/login-init -> /api/otp/verify -> /login-final
     */
    private bool $force2fa = true;

    /**
     * Secreto compartido con tu API Node para firmar el ticket de finalización 2FA.
     * Usa una cadena larga/aleatoria y pon la MISMA en Node.
     */
    private string $twoFASecret = 'DIDADPOL';

    /**
     * Mostrar formulario de login personalizado
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Autenticar usuario con validaciones y registro en bitácora (BD)
     * Si $force2fa = true, bloquea el login tradicional para no puentear el 2FA.
     */
    public function login(Request $request)
    {
        // --- Bloqueo del login tradicional si 2FA está forzado ---
        if ($this->force2fa) {
            return back()->withInput()->with('error', 'Debes iniciar sesión con verificación en dos pasos (revisa tu correo).');
        }
        // ----------------------------------------------------------

        // 1) Validar campos requeridos
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = strtolower($credentials['email']);
        $password = $credentials['password'];
        $ip = $request->ip();
        $ua = $request->userAgent();

        // 2) Buscar usuario (case-insensitive por si acaso)
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        // 2.1) Usuario no existe -> registrar intento fallido y responder genérico
        if (!$user) {
            $this->registrarIntentoEnBD($email, false, $ip, $ua, 'USUARIO_NO_EXISTE');
            return back()->withInput()->with('error', 'Correo o contraseña incorrectos.');
        }

        // 2.2) Si ya está INACTIVO -> registrar y bloquear
        if (strcasecmp($user->estado, 'INACTIVO') === 0) {
            $this->registrarIntentoEnBD($email, false, $ip, $ua, 'USUARIO_INACTIVO');
            return back()->withInput()->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        // 3) Verificar contraseña
        if (!Hash::check($password, $user->password)) {
            // Registrar intento fallido: el SP incrementa contador y puede bloquear al 3er fallo
            $res = $this->registrarIntentoEnBD($email, false, $ip, $ua, 'PASSWORD_INCORRECTO');
            $status = $res['status'] ?? null;
            $intentos = $res['intentos'] ?? null;

            if ($status === 'blocked') {
                // El SP ya puso estado='INACTIVO'
                return back()->withInput()->with('error', 'Usuario bloqueado por intentos fallidos. Contacta al administrador.');
            }

            // Mensaje con contador si está disponible
            $msg = 'Correo o contraseña incorrectos.';
            if (is_numeric($intentos)) {
                $msg = "Contraseña incorrecta. Intento {$intentos} de 3.";
            }

            return back()->withInput()->with('error', $msg);
        }

        // 4) Verificar rol asignado (tu lógica original)
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

        // 5) Éxito: registrar OK (resetea intentos en el SP) y autenticar
        $this->registrarIntentoEnBD($email, true, $request->ip(), $request->userAgent(), null);

        Auth::login($user, $request->filled('remember'));
        $request->session()->regenerate();

        // Guardar nombre del rol en sesión (tu lógica)
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
     * NUEVO: Finalizar login después de que tu API Node valide OTP.
     * Espera: user_id, nonce, ts (unix segundos), sig (HMAC SHA256) y remember opcional.
     * La firma: HMAC_SHA256( user_id . "." . nonce . "." . ts , $twoFASecret )
     */
    public function finalizarLogin2FA(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer'],
            'nonce'   => ['required', 'string', 'min:8'],
            'ts'      => ['required', 'integer'], // unix timestamp (segundos)
            'sig'     => ['required', 'string', 'min:32'],
            'remember'=> ['nullable', 'boolean'],
        ]);

        $userId  = (int) $request->input('user_id');
        $nonce   = $request->input('nonce');
        $ts      = (int) $request->input('ts');
        $sig     = $request->input('sig');
        $remember= (bool) $request->boolean('remember');

        // 1) Ventana de tiempo para el ticket (2 minutos)
        $now = time();
        if (abs($now - $ts) > 120) {
            return back()->with('error', 'Token de verificación expirado. Intenta nuevamente.');
        }

        // 2) Recalcular HMAC con el secreto del controlador
        $base = "{$userId}.{$nonce}.{$ts}";
        $calc = hash_hmac('sha256', $base, $this->twoFASecret);

        if (!hash_equals($calc, $sig)) {
            return back()->with('error', 'Token de verificación inválido.');
        }

        // 3) Cargar usuario y validar estado
        $user = User::find($userId);
        if (!$user) {
            return back()->with('error', 'Usuario no válido.');
        }
        if (strcasecmp($user->estado, 'INACTIVO') === 0) {
            return back()->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        // 4) Verificar rol activo
        $rol = DB::table('roles')
            ->join('role_user', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->select('roles.nombre', 'roles.estado')
            ->first();

        if (!$rol || strcasecmp($rol->estado, 'ACTIVO') !== 0) {
            return back()->with('error', 'Acceso denegado. Rol no asignado o inactivo.');
        }

        // 5) Registrar éxito en bitácora y autenticar sesión Laravel
        $this->registrarIntentoEnBD($user->email, true, $request->ip(), $request->userAgent(), '2FA_OK');

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
