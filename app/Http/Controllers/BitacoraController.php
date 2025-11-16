<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        // ============================
        // 1) Construir parámetros para el API
        // ============================
        $params = array_filter([
            'modo'             => $request->input('modo', 'general'),
            'usuario'          => $request->input('usuario'),
            'usuario_id'       => $request->input('usuario_id'),
            'tipo_evento'      => $request->input('tipo_evento'),
            'accion'           => $request->input('accion'),
            'tabla'            => $request->input('tabla'),
            'ip'               => $request->input('ip'),
            'q'                => $request->input('q'),
            'desde'            => $request->input('desde'),
            'hasta'            => $request->input('hasta'),
            'incluir_sesiones' => $request->input('incluir_sesiones'),
            'page'             => $request->input('page', 1),
            'limit'            => $request->input('limit', 20),
            'sort'             => $request->input('sort', 'fecha'),
            'dir'              => $request->input('dir', 'desc'),
        ], fn ($v) => !is_null($v) && $v !== '');

        // Guardamos el modo actual por si el API no lo regresa
        $modoSolicitado = $params['modo'] ?? 'general';

        // ============================
        // 2) Llamar al API de Bitácora (Node)
        // ============================
        try {
           
            $apiBase = rtrim(env('RRHH_API_URL', 'https://rrhh-didadpol-1.onrender.com'), '/');
            $url     = $apiBase . '/api/bitacora';

            $response = Http::timeout(20)->get($url, $params);
        } catch (\Throwable $e) {

            // Si es AJAX devolvemos partial coherente aunque vacío
            if ($request->ajax()) {
                return response()->view('bitacora.partials.resultados', [
                    'registros'  => [],
                    'meta'       => [
                        'page'      => (int)($params['page'] ?? 1),
                        'last_page' => 1,
                        'limit'     => (int)($params['limit'] ?? 20),
                        'sort'      => $params['sort'] ?? 'fecha',
                        'dir'       => $params['dir'] ?? 'desc',
                        'modo'      => $modoSolicitado,
                        'total'     => 0,
                    ],
                    'modoActual' => $modoSolicitado,
                ], 500);
            }

            return back()->with('error', 'No se pudieron obtener los registros de la bitácora.');
        }

        if (!$response->successful()) {
            if ($request->ajax()) {
                return response()->view('bitacora.partials.resultados', [
                    'registros'  => [],
                    'meta'       => [
                        'page'      => (int)($params['page'] ?? 1),
                        'last_page' => 1,
                        'limit'     => (int)($params['limit'] ?? 20),
                        'sort'      => $params['sort'] ?? 'fecha',
                        'dir'       => $params['dir'] ?? 'desc',
                        'modo'      => $modoSolicitado,
                        'total'     => 0,
                    ],
                    'modoActual' => $modoSolicitado,
                ], $response->status());
            }

            return back()->with('error', 'No se pudieron obtener los registros de la bitácora.');
        }

        // ============================
        // 3) Procesar respuesta del API
        // ============================
        $payload   = $response->json();
        $registros = $payload['data'] ?? [];
        $metaApi   = $payload['meta'] ?? [];

        // Normalizar meta con defaults para que la vista siempre tenga todo
        $meta = array_merge([
            'page'      => (int)($params['page'] ?? 1),
            'last_page' => 1,
            'limit'     => (int)($params['limit'] ?? 20),
            'sort'      => $params['sort'] ?? 'fecha',
            'dir'       => $params['dir'] ?? 'desc',
            'modo'      => $modoSolicitado,
            'total'     => 0,
        ], $metaApi);

        $modoActual = $meta['modo'] ?? $modoSolicitado;

        // ============================
        // 4) Si es AJAX → solo el partial
        // ============================
        if ($request->ajax()) {
            return view('bitacora.partials.resultados', [
                'registros'  => $registros,
                'meta'       => $meta,
                'modoActual' => $modoActual,
            ]);
        }

        // ============================
        // 5) Carga normal → vista completa
        // ============================
        return view('bitacora.index', [
            'registros'  => $registros,
            'meta'       => $meta,
            'filtros'    => $params,      // solo lo que realmente usamos
            'modoActual' => $modoActual,
        ]);
    }
}
