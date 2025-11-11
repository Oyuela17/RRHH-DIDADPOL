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
    public function showLoginForm()
    {
        return view('auth.login');
    }

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

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user) {
            $this->registrarIntentoEnBD($email, false, $ip, $ua, 'USUARIO_NO_EXISTE');
            return back()->withInput()->with('error', 'Correo o contraseña incorrectos.');
        }

        if (strcasecmp($user->estado, 'INACTIVO') === 0) {
            $this->registrarIntentoEnBD($email, false, $ip, $ua, 'USUARIO_INACTIVO');
            return back()->withInput()->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
        }

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

// ✅ En producción: usar la API Node.js fija (Render)
try {
    $baseUrl = 'https://rrhh-didadpol-1.onrender.com'; // <-- URL fija de la API Node.js
    $timeout = 15;
    $adminToken = env('ADMIN_TOKEN');

    $http = Http::timeout($timeout);
    if (!empty($adminToken)) {
        $http = $http->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
        ]);
    }

    // Enviar petición a la API Node para iniciar 2FA
    $resp = $http->post($baseUrl . '/api/2fa/start', [
        'email' => $email,
    ]);

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

    return back()
        ->with('pending_2fa', true)
        ->with('masked_email', $data['masked_email'] ?? null)
        ->with('expires_in', $data['expires_in'] ?? null)
        ->with('cooldown', $data['cooldown_resend'] ?? null);
} catch (\Throwable $e) {
    \Log::error('2FA start error: ' . $e->getMessage());
    return back()->with('error', 'Ocurrió un problema iniciando la verificación.');
}


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

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
