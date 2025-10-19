<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EstadoUsuarioController extends Controller
{
    /**
     * Devuelve el estado actual del usuario autenticado y su rol
     */
    public function verificarEstado()
    {
        if (Auth::check()) {
            $usuario = Auth::user();
            $estadoUsuario = $usuario->estado;

            // Obtener el estado del rol del usuario
            $estadoRol = DB::table('roles')
                ->join('role_user', 'roles.id', '=', 'role_user.role_id')
                ->where('role_user.user_id', $usuario->id)
                ->value('roles.estado');

            return response()->json([
                'estado' => $estadoUsuario,
                'estado_rol' => $estadoRol ?? 'SIN ROL'
            ]);
        }

        return response()->json([
            'estado' => 'INACTIVO',
            'estado_rol' => 'INACTIVO'
        ]);
    }
}
