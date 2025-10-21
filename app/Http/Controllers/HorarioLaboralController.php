<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class HorarioLaboralController extends Controller
{
    private $apiUrl = 'https://rrhh-didadpol-1.onrender.com/api/horarios';

    public function index(Request $request)
    {
        $busqueda = strtoupper((string) $request->input('busqueda', ''));
        $ordenar  = $request->input('ordenar', 'nombre'); // 'nombre'|'fecha'
        $cantidad = (int) $request->input('cantidad', 5);
        $cantidad = $cantidad > 0 ? $cantidad : 5;

        try {
            $response = Http::timeout(10)->get($this->apiUrl . '?detalles=true');
            if (!$response->successful()) {
                return back()->with('error', 'No se pudo obtener la lista de horarios.');
            }

            $datos = collect($response->json() ?? []);

            if ($busqueda !== '') {
                $datos = $datos->filter(fn($h) =>
                    Str::contains(strtoupper($h['nom_horario'] ?? ''), $busqueda)
                );
            }

            $datos = $ordenar === 'fecha'
                ? $datos->sortByDesc('fec_registro')
                : $datos->sortBy('nom_horario');

            $pagina = (int) $request->get('page', 1);
            $items  = $datos->values()->forPage($pagina, $cantidad);

            $paginador = new LengthAwarePaginator(
                $items, $datos->count(), $cantidad, $pagina,
                ['path' => route('horarios.index'), 'query' => $request->query()]
            );

            return view('horarios_laborales.index', [
                'horarios' => $paginador,
                'busqueda' => $busqueda,
                'ordenar'  => $ordenar,
                'cantidad' => $cantidad
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Servicio no disponible al listar horarios.');
        }
    }

    public function store(Request $request)
    {
        // Normalizar dias_semana si viene como string "LU,MA,..." o "LUNES, MARTES"
        $input = $request->all();
        $input['dias_semana'] = $this->normalizarDias($request->input('dias_semana'));

        // Validaciones
        $validator = Validator::make(
            $input,
            [
                'nom_horario'  => ['required','string','max:50','regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'],
                'hora_inicio'  => ['required','date_format:H:i'],
                'hora_final'   => ['required','date_format:H:i','after:hora_inicio'],
                'dias_semana'  => ['required','array','min:1'],
                'dias_semana.*'=> ['in:LU,MA,MI,JU,VI,SA,DO'],
            ],
            [
                'nom_horario.required' => 'El nombre del horario es obligatorio.',
                'nom_horario.regex'    => 'El nombre solo permite letras y espacios.',
                'hora_inicio.required' => 'La hora de inicio es obligatoria.',
                'hora_inicio.date_format' => 'La hora de inicio debe tener el formato HH:MM.',
                'hora_final.required'  => 'La hora final es obligatoria.',
                'hora_final.date_format'=> 'La hora final debe tener el formato HH:MM.',
                'hora_final.after'     => 'La hora final debe ser posterior a la hora de inicio.',
                'dias_semana.required' => 'Debes seleccionar al menos un día.',
                'dias_semana.*.in'     => 'Día inválido. Usa LU, MA, MI, JU, VI, SA o DO.',
            ]
        );

       
        $payload = [
            'nom_horario'   => $input['nom_horario'],
            'hora_inicio'   => $input['hora_inicio'],
            'hora_final'    => $input['hora_final'],
            'dias_semana'   => $input['dias_semana'],
            'usr_registro'  => auth()->user()->name ?? 'admin',
        ];

        try {
            $response = Http::timeout(10)->asJson()->post($this->apiUrl, $payload);

            return $response->successful()
                ? redirect()->route('horarios.index')->with('success', 'Horario creado correctamente.')
                : back()->with('error', 'Error al crear el horario.')->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Servicio no disponible al crear.')->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $input['dias_semana'] = $this->normalizarDias($request->input('dias_semana'));

        $validator = Validator::make(
            $input,
            [
                'nom_horario'  => ['required','string','max:50','regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/'],
                'hora_inicio'  => ['required','date_format:H:i'],
                'hora_final'   => ['required','date_format:H:i','after:hora_inicio'],
                'dias_semana'  => ['required','array','min:1'],
                'dias_semana.*'=> ['in:LU,MA,MI,JU,VI,SA,DO'],
            ],
            [
                'nom_horario.required' => 'El nombre del horario es obligatorio.',
                'nom_horario.regex'    => 'El nombre solo permite letras y espacios.',
                'hora_inicio.required' => 'La hora de inicio es obligatoria.',
                'hora_inicio.date_format' => 'La hora de inicio debe tener el formato HH:MM.',
                'hora_final.required'  => 'La hora final es obligatoria.',
                'hora_final.date_format'=> 'La hora final debe tener el formato HH:MM.',
                'hora_final.after'     => 'La hora final debe ser posterior a la hora de inicio.',
                'dias_semana.required' => 'Debes seleccionar al menos un día.',
                'dias_semana.*.in'     => 'Día inválido. Usa LU, MA, MI, JU, VI, SA o DO.',
            ]
        );

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->with('advertencia', 'Revisa los campos: solo letras en el nombre; horas válidas (HH:MM); días de la semana válidos.')
                ->withInput();
        }

        $payload = [
            'nom_horario'  => $input['nom_horario'],
            'hora_inicio'  => $input['hora_inicio'],
            'hora_final'   => $input['hora_final'],
            'dias_semana'  => $input['dias_semana'],
            'usr_modificacion' => auth()->user()->name ?? 'admin',
        ];

        try {
            $response = Http::timeout(10)->asJson()->put("{$this->apiUrl}/{$id}", $payload);

            return $response->successful()
                ? redirect()->route('horarios.index')->with('success', 'Horario actualizado correctamente.')
                : back()->with('error', 'Error al actualizar el horario.')->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Servicio no disponible al actualizar.')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $response = Http::timeout(10)->delete("{$this->apiUrl}/{$id}");

            return $response->successful()
                ? redirect()->route('horarios.index')->with('success', 'Horario eliminado correctamente.')
                : back()->with('error', 'Error al eliminar el horario.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Servicio no disponible al eliminar.');
        }
    }

    /**
     * Normaliza el campo dias_semana aceptando:
     *  - Array de strings
     *  - String "LU,MA,..." o "LUNES,MARTES,..."
     * Devuelve array de abreviaturas válidas: [LU, MA, MI, JU, VI, SA, DO]
     */
    private function normalizarDias($dias)
    {
        $validos = ['LU','MA','MI','JU','VI','SA','DO'];
        $mapaLargos = [
            'LUNES' => 'LU', 'MARTES' => 'MA', 'MIERCOLES' => 'MI', 'MIÉRCOLES' => 'MI',
            'JUEVES' => 'JU', 'VIERNES' => 'VI', 'SABADO' => 'SA', 'SÁBADO' => 'SA', 'DOMINGO' => 'DO'
        ];

        if (is_null($dias)) return [];

        // Si viene como string, separa por comas
        if (is_string($dias)) {
            $dias = array_map('trim', explode(',', $dias));
        }

        if (!is_array($dias)) return [];

        $resultado = [];
        foreach ($dias as $d) {
            $u = strtoupper(trim((string)$d));
            // Si ya es abreviatura válida
            if (in_array($u, $validos, true)) {
                $resultado[] = $u;
                continue;
            }
            // Si viene en largo, mapea
            if (isset($mapaLargos[$u])) {
                $resultado[] = $mapaLargos[$u];
            }
        }

        // Quita duplicados y reindexa
        return array_values(array_unique($resultado));
    }
}