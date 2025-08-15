<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UsuarioController extends Controller
{
    public function create()
    {
        try {
            $response = Http::get('http://localhost:3000/api/empleados');
            $empleados = $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            $empleados = [];
        }

        return view('auth.register', compact('empleados'));
    }

    public function store(Request $request)
    {
        // ✅ Validar todos los campos
        $request->validate([
            'persona_id'       => 'required|integer',
            'nombre_completo'  => 'required|string|max:255',
            'correo_personal'  => 'required|email|max:255',
        ]);

        try {
            // ✅ Enviar también cod_persona
            $response = Http::post('http://localhost:3000/api/registrar-usuario', [
                'cod_persona'      => $request->persona_id,
                'nombre_completo'  => $request->nombre_completo,
                'correo_personal'  => $request->correo_personal,
            ]);

            if ($response->successful()) {
                if ($request->expectsJson() || $request->isJson()) {
                    return response()->json([
                        'success' => true,
                        'mensaje' => 'Usuario registrado correctamente. Revisa el correo personal.',
                        'redirigir' => route('login')
                    ]);
                }

                return redirect()->route('usuario.create')
                    ->with('success', 'Usuario registrado correctamente. Revisa el correo personal.');
            } else {
                $mensaje = $response->json('message') ?? 'Error al registrar usuario.';
                if ($request->expectsJson() || $request->isJson()) {
                    return response()->json(['success' => false, 'errores' => [$mensaje]], 422);
                }

                return back()->withErrors(['error' => $mensaje]);
            }
        } catch (\Exception $e) {
            $mensaje = 'Error de conexión con el servidor: ' . $e->getMessage();
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json(['success' => false, 'errores' => [$mensaje]], 500);
            }

            return back()->withErrors(['error' => $mensaje]);
        }
    }
}
