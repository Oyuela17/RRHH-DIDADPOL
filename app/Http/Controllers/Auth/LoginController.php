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
     * Autenticar usuario con todas las validaciones de seguridad
     */
    public function login(Request $request)
    {
        // Validar campos requeridos
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Buscar usuario por email
        $user = User::where('email', $credentials['email'])->first();

        // Validar si el usuario existe y la contraseña es correcta
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withInput()->with('error', 'Correo o contraseña incorrectos.');
        }

        // Verificar estado del usuario
        if (strtoupper($user->estado) !== 'ACTIVO') {
            return back()->withInput()->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        // Verificar si tiene rol asignado
        $rol = DB::table('roles')
            ->join('role_user', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->select('roles.nombre', 'roles.estado')
            ->first();

        if (!$rol) {
            return back()->withInput()->with('error', 'Acceso denegado. No tienes un rol asignado.');
        }

        // Verificar estado del rol
        if (strtoupper($rol->estado) !== 'ACTIVO') {
            return back()->withInput()->with('error', 'Acceso denegado. Tu rol está inactivo.');
        }

        // ✅ Autenticación segura solo si todo está validado
        Auth::login($user, $request->filled('remember'));

        // Guardar nombre del rol en sesión
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
}
