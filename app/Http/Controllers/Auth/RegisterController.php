<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /**
     * Redirección posterior (no usada en este flujo, pero mantenida por compatibilidad).
     * @var string
     */
    protected $redirectTo = '/home';

    public function __construct()
    {
        // Este registro lo hace un admin autenticado desde el panel
        $this->middleware('auth');
    }

    /**
     * Muestra la vista (si usas esta ruta para renderizar el Blade).
     * Puedes seguir usando tu controlador/vista actual; este método es opcional.
     */
    public function create()
    {
        // return view('auth.register'); // si lo necesitaras
        abort(404);
    }

    /**
     * Proxy al backend Node para registrar usuario.
     * Recibe JSON: cod_persona, nombre_completo, correo_personal, usar_correo_institucional
     */
    public function store(Request $request)
    {
        // Acepta tanto cod_persona como persona_id (por si quedó algún form viejo)
        $payload = [
            'cod_persona' => $request->input('cod_persona') ?: $request->input('persona_id'),
            'nombre_completo' => $request->input('nombre_completo'),
            'correo_personal' => $request->input('correo_personal'),
            'usar_correo_institucional' => $request->boolean('usar_correo_institucional', true),
        ];

        // Validaciones de frontend/seguridad
        $request->merge($payload); // para que Validator lea lo mismo
        $validated = $request->validate([
            'cod_persona' => ['required'],
            'nombre_completo' => ['required', 'string', 'max:255'],
            'correo_personal' => ['required', 'email:rfc,dns', 'max:255'],
            'usar_correo_institucional' => ['required', Rule::in([true, false])],
        ], [
            'cod_persona.required' => 'Debes seleccionar una persona.',
            'nombre_completo.required' => 'El nombre es requerido.',
            'correo_personal.required' => 'El correo personal es requerido.',
            'correo_personal.email' => 'El correo personal no tiene un formato válido.',
        ]);

        // Endpoint de tu API Node
        $base = rtrim(env('NODE_API_BASE', ''), '/');
        if (empty($base)) {
            return response()->json([
                'error' => 'NODE_API_BASE no está configurado en el servidor.'
            ], 500);
        }

        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->asJson()
                ->post($base . '/api/registrar-usuario', [
                    'cod_persona' => (string) $validated['cod_persona'],
                    'nombre_completo' => (string) $validated['nombre_completo'],
                    'correo_personal' => strtolower(trim($validated['correo_personal'])),
                    'usar_correo_institucional' => (bool) $validated['usar_correo_institucional'],
                ]);

            // Reenvía tal cual el status y el JSON para que el front decida qué alertar
            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            // Error de red / timeout / excepción
            return response()->json([
                'error' => 'No se pudo contactar la API de registro.',
                'detalle' => $e->getMessage(),
            ], 502);
        }
    }
}
