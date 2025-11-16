<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
            if (Auth::check()) {
                // Obtener el rol_id del usuario desde tabla intermedia
                $rolId = DB::table('role_user')
                    ->where('user_id', Auth::id())
                    ->value('role_id');

                $modulosPermitidos = DB::table('permisos')
                    ->join('modulos', 'permisos.modulo_id', '=', 'modulos.id')
                    ->where('permisos.rol_id', $rolId)
                    ->where('permisos.tiene_acceso', true)
                    ->pluck('modulos.nombre')
                    ->map(fn($nombre) => strtoupper($nombre))
                    ->toArray();

                $view->with('modulosPermitidos', $modulosPermitidos);
            } else {
                $view->with('modulosPermitidos', []);
            }
        });
    }
}
