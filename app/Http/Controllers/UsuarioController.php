<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    /**
     * Opcional: si este registro solo lo hace un admin logueado,
     * descomenta el middleware 'auth'.
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function create()
    {
        $base = rtrim(env('NODE_API_BASE', 'https://rrhh-didadpol-1.onrender.com'), '/');

        try {
            $response  = Http::timeout(15)->acceptJson()->get($base . '/api/empleados');
            $empleados = $response->successful() ? $response->json() : [];
        } catch (\Throwable $e) {
            $empleados = [];
        }

        return view('auth.register', compact('empleados'));
    }

    public function store(Request $request)
    {
        // Normaliza/une nombres del front: cod_persona vs persona_id
        $payload = [
            'cod_persona'              => $request->input('cod_persona') ?: $request->input('persona_id'),
            'nombre_completo'          => $request->input('nombre_completo'),
            'correo_personal'          => strtolower(trim((string) $request->input('correo_personal'))),
            // Si no viene, por defecto true (crear institucional)
            'usar_correo_institucional'=> filter_var($request->input('usar_correo_institucional', true), FILTER_VALIDATE_BOOLEAN),
        ];

        // Valida SIN redirigir (si falla -> 422 JSON)
        $validator = Validator::make($payload, [
            'cod_persona'               => ['required'],
            'nombre_completo'           => ['required', 'string', 'max:255'],
            'correo_personal'           => ['required', 'email:rfc,dns', 'max:255'],
            'usar_correo_institucional' => ['required', 'boolean'],
        ], [
            'cod_persona.required'      => 'Debes seleccionar una persona.',
            'nombre_completo.required'  => 'El nombre es requerido.',
            'correo_personal.required'  => 'El correo personal es requerido.',
            'correo_personal.email'     => 'El correo personal no es válido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Llama a la API Node
        $base = rtrim(env('NODE_API_BASE', 'https://rrhh-didadpol-1.onrender.com'), '/');
        if (empty($base)) {
            return response()->json([
                'success' => false,
                'error'   => 'NODE_API_BASE no está configurado en el servidor.',
            ], 500);
        }

        try {
            $api = Http::timeout(25)
                ->acceptJson()
                ->asJson()
                ->post($base . '/api/registrar-usuario', [
                    'cod_persona'               => (string) $payload['cod_persona'],
                    'nombre_completo'           => (string) $payload['nombre_completo'],
                    'correo_personal'           => (string) $payload['correo_personal'],
                    'usar_correo_institucional' => (bool) $payload['usar_correo_institucional'],
                ]);

            // Reenvía tal cual el status y el JSON de la API Node
            return response()->json($api->json(), $api->status());

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'No se pudo contactar la API de registro.',
                'detail'  => $e->getMessage(),
            ], 502);
        }
    }
}
