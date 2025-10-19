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
     * Mostrar formulario de login personalizado
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Autenticar usuario con validaciones y registro en bitácora (BD)
     */
    public function login(Request $request)
    {
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
            // (Opcional) podrías registrar este evento en bitácora si quieres
            return back()->withInput()->with('error', 'Acceso denegado. No tienes un rol asignado.');
        }

        if (strcasecmp($rol->estado, 'ACTIVO') !== 0) {
            // (Opcional) podrías registrar este evento en bitácora si quieres
            return back()->withInput()->with('error', 'Acceso denegado. Tu rol está inactivo.');
        }

        // 5) Éxito: registrar OK (resetea intentos en el SP) y autenticar
        $this->registrarIntentoEnBD($email, true, $ip, $ua, null);

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
     * Invoca la función SQL public.login_registrar_intento(...)
     * Devuelve el JSON decodificado o [] si algo falla.
     */
    private function registrarIntentoEnBD(string $email, bool $exito, string $ip, string $ua, ?string $motivo): array
    {
        try {
            // Nota: en PostgreSQL DB::select devuelve array de stdClass.
            // La función retorna un JSONB; lo leemos como "result".
            $rows = DB::select(
                "SELECT public.login_registrar_intento(?, ?, ?, ?, ?) AS result",
                [$email, $exito, $ip, $ua, $motivo]
            );

            if (!empty($rows) && property_exists($rows[0], 'result')) {
                $decoded = json_decode($rows[0]->result, true);
                return is_array($decoded) ? $decoded : [];
            }
        } catch (\Throwable $e) {
            // Si el SP no existe o falla, no rompemos el login.
            // Puedes loguearlo si deseas: \Log::warning('login_registrar_intento error: '.$e->getMessage());
        }
        return [];
        }
}
