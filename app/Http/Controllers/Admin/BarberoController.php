<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barbero;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BarberoController extends Controller
{
    public function index(): View
    {
        return view('admin.barberos', [
            'barberos' => Barbero::with('horarios')->orderBy('nombre')->get(),
            'dias' => Horario::DIAS,
        ]);
    }

    public function guardar(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'especialidad' => ['nullable', 'string', 'max:80'],
        ]);

        $barbero = Barbero::create($datos + ['activo' => true]);

        // Horario por defecto: lunes a sábado de 9:00 a 13:00 y de 14:00 a 20:00.
        foreach (range(1, 6) as $dia) {
            $barbero->horarios()->createMany([
                ['dia_semana' => $dia, 'hora_inicio' => '09:00', 'hora_fin' => '13:00'],
                ['dia_semana' => $dia, 'hora_inicio' => '14:00', 'hora_fin' => '20:00'],
            ]);
        }

        return back()->with('ok', "Barbero «{$barbero->nombre}» agregado con el horario estándar.");
    }

    /**
     * Actualiza datos y horario semanal. El horario llega como
     * horario[dia][0|1][inicio|fin] (turno mañana y turno tarde).
     */
    public function actualizar(Request $request, Barbero $barbero)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'especialidad' => ['nullable', 'string', 'max:80'],
            'horario' => ['array'],
            'horario.*.*.inicio' => ['nullable', 'date_format:H:i'],
            'horario.*.*.fin' => ['nullable', 'date_format:H:i'],
        ]);

        $bloques = [];
        foreach ($datos['horario'] ?? [] as $dia => $turnos) {
            foreach ($turnos as $turno) {
                $inicio = $turno['inicio'] ?? null;
                $fin = $turno['fin'] ?? null;
                if (! $inicio && ! $fin) {
                    continue;
                }
                if (! $inicio || ! $fin || $inicio >= $fin) {
                    throw ValidationException::withMessages([
                        'horario' => 'Revisa el '.mb_strtolower(Horario::DIAS[$dia] ?? 'día').': la hora de inicio debe ser menor que la de fin.',
                    ]);
                }
                $bloques[] = ['dia_semana' => (int) $dia, 'hora_inicio' => $inicio, 'hora_fin' => $fin];
            }
        }

        DB::transaction(function () use ($barbero, $datos, $request, $bloques) {
            $barbero->update([
                'nombre' => $datos['nombre'],
                'especialidad' => $datos['especialidad'] ?? null,
                'activo' => $request->boolean('activo'),
            ]);
            $barbero->horarios()->delete();
            $barbero->horarios()->createMany($bloques);
        });

        return back()->with('ok', "Datos de «{$barbero->nombre}» guardados.");
    }
}
