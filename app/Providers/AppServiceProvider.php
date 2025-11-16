<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {

            // Si no está autenticado, evitar errores
            if (!Auth::check()) {
                $view->with('modulosPermitidos', []);
                $view->with('accionesPermitidas', []);
                return;
            }

            // ================================
            // OBTENER EL ROL DEL USUARIO
            // ================================
            $rolId = DB::table('role_user')
                ->where('user_id', Auth::id())
                ->value('role_id');

            if (!$rolId) {
                $view->with('modulosPermitidos', []);
                $view->with('accionesPermitidas', []);
                return;
            }

            // ================================
            // TRAER PERMISOS DESDE NODE
            // ================================
            try {
                $response = Http::get("http://localhost:3000/api/permisos/" . $rolId);

                if ($response->failed()) {
                    $view->with('modulosPermitidos', []);
                    $view->with('accionesPermitidas', []);
                    return;
                }

                $permisos = $response->json();

            } catch (\Throwable $e) {
                // Si falla el servidor Node, no tumba Laravel
                $view->with('modulosPermitidos', []);
                $view->with('accionesPermitidas', []);
                return;
            }

            // ================================
            // ARMAR LOS PERMISOS PARA BLADE
            // ================================
            $modulosPermitidos  = [];
            $accionesPermitidas = [];

            foreach ($permisos as $p) {
                $nombreModulo = strtoupper($p['nombre']);

                // PARA EL MENÚ
                if (!empty($p['tiene_acceso'])) {
                    $modulosPermitidos[] = $nombreModulo;
                }

                // PARA BOTONES DE CREAR/EDITAR/ELIMINAR
                $accionesPermitidas[$nombreModulo] = [
                    'crear'      => (bool)($p['puede_crear'] ?? false),
                    'actualizar' => (bool)($p['puede_actualizar'] ?? false),
                    'eliminar'   => (bool)($p['puede_eliminar'] ?? false),
                ];
            }

            // ================================
            // COMPARTIR CON TODAS LAS VISTAS
            // ================================
            $view->with('modulosPermitidos', $modulosPermitidos);
            $view->with('accionesPermitidas', $accionesPermitidas);
        });
    }
}
