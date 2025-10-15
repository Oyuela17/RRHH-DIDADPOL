<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        // (Opcionales) filtros por querystring: q, desde, hasta, accion, tabla, page, pageSize
        $params = array_filter([
            'q'        => $request->input('q'),
            'desde'    => $request->input('desde'),
            'hasta'    => $request->input('hasta'),
            'accion'   => $request->input('accion'),
            'tabla'    => $request->input('tabla'),
            'page'     => $request->input('page'),
            'pageSize' => $request->input('pageSize'),
        ], fn($v) => !is_null($v) && $v !== '');

        $response = Http::get('http://localhost:3000/api/bitacora', $params);

        if ($response->successful()) {
            $bitacora = $response->json(); // Array con: fecha, usuario_nombre, accion, tabla, descripcion, ip_origen
            return view('bitacora.index', compact('bitacora'));
        }

        return back()->with('error', 'No se pudieron obtener los registros de la bitácora.');
    }
}
