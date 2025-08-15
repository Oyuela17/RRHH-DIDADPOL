<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class BackupsUiController extends Controller
{
    private string $apiBase;
    private string $apiBackups;
    private string $apiRestoreUpload;
    private string $apiRestoreUse;
    private array  $headers;

    public function __construct()
    {
        $this->middleware('auth');

        // .env / config/services.php
        $this->apiBase          = rtrim(config('services.backups.base_url', env('BACKUPS_API', 'http://localhost:3000')), '/');
        $this->apiBackups       = $this->apiBase . '/api/backups';
        $this->apiRestoreUpload = $this->apiBase . '/api/restore/upload';
        $this->apiRestoreUse    = $this->apiBase . '/api/restore/use';

        $this->headers = [
            'X-Admin-Token' => env('BACKUPS_ADMIN_TOKEN', 'tu_token_super_secreto'),
        ];
    }

    /** Vista principal (historial + tarjetas superiores) */
    public function index()
    {
        $files = [];
        try {
            $resp = Http::withHeaders($this->headers)->timeout(15)->get($this->apiBackups);
            if ($resp->ok()) {
                $files = $resp->json() ?? [];
            }
        } catch (\Throwable $e) {
            // Silencioso, caemos al fallback local
        }

        // Fallback local si la API no respondió
        if (empty($files)) {
            $files = DB::table('public.backup')
                ->orderByDesc('id')
                ->get()
                ->map(fn ($r) => (array) $r)
                ->toArray();
        }

        $ultimo = $files[0] ?? null;
        $ultimoBackup = $ultimo ? [
            'fecha'     => $ultimo['fecha'] ?? null,
            'estado'    => $ultimo['estado'] ?? null,
            'ubicacion' => $ultimo['ruta_archivo'] ?? null,
        ] : null;

        return view('backups.index', compact('files', 'ultimoBackup'));
    }

    /** Ejecutar backup (solo BD) */
    public function run(Request $r)
    {
        $tipo      = $r->input('tipo', 'solo_bd');
        $usuarioId = $r->user()->id ?? null;

        try {
            $resp = Http::withHeaders($this->headers)
                ->timeout(120)
                ->post($this->apiBackups, [
                    'tipo'       => $tipo,
                    'usuario_id' => $usuarioId,
                ]);

            if ($resp->ok()) {
                return back()->with('ok', 'Backup creado correctamente. Refresca para ver el nuevo registro.');
            }

            $jsonErr = $resp->json();
            return back()->with('err', ($jsonErr['error'] ?? 'No se pudo crear el backup.'));
        } catch (\Throwable $e) {
            return back()->with('err', 'No se pudo contactar la API: ' . $e->getMessage());
        }
    }

    /** Descargar backup */
    public function download($id)
    {
        $url = "{$this->apiBackups}/{$id}/download";
        try {
            $res = Http::withHeaders($this->headers)
                ->timeout(300)
                ->withOptions(['stream' => true])
                ->get($url);

            if ($res->ok()) {
                // Tomar nombre del header si viene
                $filename = "backup_{$id}.zip";
                $disp = $res->header('Content-Disposition');
                if ($disp && preg_match('/filename="?([^"]+)"?/i', $disp, $m)) {
                    $filename = $m[1];
                }

                return response()->streamDownload(function () use ($res) {
                    $body = $res->getBody();
                    while (! $body->eof()) {
                        echo $body->read(8192);
                        flush();
                    }
                }, $filename, [
                    'Content-Type' => $res->header('Content-Type') ?? 'application/octet-stream',
                ]);
            }
        } catch (\Throwable $e) {
            // Silencioso, caemos al plan B local
        }

        // Fallback local directo al archivo
        $row = DB::table('public.backup')->where('id', $id)->first();
        abort_unless($row, 404, 'Backup no encontrado.');
        $path = $row->ruta_archivo;
        $name = $row->nombre_archivo ?? basename($path);
        abort_unless(is_file($path), 404, 'Archivo no encontrado en disco.');

        return Response::download($path, $name);
    }

    /** Eliminar backup */
    public function destroy($id)
    {
        try {
            $resp = Http::withHeaders($this->headers)->timeout(60)->delete("{$this->apiBackups}/{$id}");
            if ($resp->ok()) {
                return back()->with('ok', 'Backup eliminado.');
            }
        } catch (\Throwable $e) {
            // seguimos al fallback
        }

        // Fallback local
        $row = DB::table('public.backup')->where('id', $id)->first();
        if ($row) {
            try {
                if (is_file($row->ruta_archivo)) @unlink($row->ruta_archivo);
            } catch (\Throwable $e) {}
            DB::table('public.backup')->where('id', $id)->delete();
            return back()->with('ok', 'Backup eliminado (local).');
        }

        return back()->with('err', 'No se pudo eliminar.');
    }

    /** Restaurar subiendo archivo (.sql / .dump / .backup / .zip) */
    public function restoreUpload(Request $req)
    {
        // Acepta extensiones modernas del index.js
        $req->validate(['file' => 'required|file|mimes:sql,zip,dump,backup']);

        try {
            $res = Http::withHeaders($this->headers)
                ->timeout(600) // restaurar puede tomar tiempo
                ->attach(
                    'file',
                    file_get_contents($req->file('file')->getRealPath()),
                    $req->file('file')->getClientOriginalName()
                )
                ->post($this->apiRestoreUpload);

            if ($res->ok()) {
                $j = $res->json();
                $logs = $this->stringifyLogs($j['logs'] ?? null);
                return back()
                    ->with('ok', 'Restauración completada.')
                    ->with('restore_log', $logs);
            }

            $j = $res->json();
            $detail = $j['detail'] ?? ($j['error'] ?? 'No se pudo restaurar.');
            $logs = $this->stringifyLogs($j['logs'] ?? null);

            return back()
                ->with('err', 'Falló restauración: ' . $detail)
                ->with('restore_log', $logs);
        } catch (\Throwable $e) {
            return back()->with('err', 'No se pudo contactar la API: ' . $e->getMessage());
        }
    }

    /** Restaurar usando backup existente (en disco del servidor) */
    public function restoreUse($id)
    {
        $row = DB::table('public.backup')->where('id', $id)->first();
        abort_unless($row, 404, 'Backup no encontrado.');

        try {
            $res = Http::withHeaders($this->headers)
                ->timeout(600)
                ->post($this->apiRestoreUse, [
                    'path' => $row->ruta_archivo,
                ]);

            if ($res->ok()) {
                $j = $res->json();
                $logs = $this->stringifyLogs($j['logs'] ?? null);
                return back()
                    ->with('ok', 'Restauración completada.')
                    ->with('restore_log', $logs);
            }

            $j = $res->json();
            $detail = $j['detail'] ?? ($j['error'] ?? 'No se pudo restaurar.');
            $logs = $this->stringifyLogs($j['logs'] ?? null);

            return back()
                ->with('err', 'Falló restauración: ' . $detail)
                ->with('restore_log', $logs);
        } catch (\Throwable $e) {
            return back()->with('err', 'No se pudo contactar la API: ' . $e->getMessage());
        }
    }

    /** Guardar configuración (placeholder) */
    public function saveSchedule(Request $req)
    {
        // Aquí guardarías config en DB si aplica
        return back()->with('ok', 'Configuración guardada.');
    }

    /** Probar configuración (placeholder) */
    public function testSchedule()
    {
        return back()->with('ok', 'Prueba de respaldo automático ejecutada.');
    }

    /** ========= Helpers ========= */

    /**
     * Convierte el array de logs devuelto por la API Node a un string plano.
     * Formato esperado:
     *  [
     *    { file: '/ruta/a.sql', ok: true, stdout: '...', stderr: '...' },
     *    { ok: true, stdout: '...', stderr: '...' } // cuando no es ZIP hay un solo item
     *  ]
     */
    private function stringifyLogs($logs): ?string
    {
        if (!$logs) return null;

        // logs puede ser un array de items o un solo item
        if (!is_array($logs)) {
            return is_string($logs) ? $logs : json_encode($logs, JSON_PRETTY_PRINT);
        }

        $out = [];
        foreach ($logs as $i => $item) {
            if (!is_array($item)) {
                $out[] = is_string($item) ? $item : json_encode($item, JSON_PRETTY_PRINT);
                continue;
            }
            $name = $item['file'] ?? ("step_" . ($i + 1));
            $ok   = array_key_exists('ok', $item) ? ($item['ok'] ? 'OK' : 'FAIL') : 'OK';
            $stdout = trim((string)($item['stdout'] ?? ''));
            $stderr = trim((string)($item['stderr'] ?? ''));

            $chunk = "[$ok] $name";
            if ($stdout !== '') $chunk .= "\nSTDOUT:\n" . $stdout;
            if ($stderr !== '') $chunk .= "\nSTDERR:\n" . $stderr;
            $out[] = $chunk;
        }
        return implode("\n\n-------------------------\n\n", $out);
    }
}

