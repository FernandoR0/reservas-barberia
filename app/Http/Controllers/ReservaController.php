<?php

namespace App\Http\Controllers;

use App\Enums\EstadoReserva;
use App\Models\Barbero;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Services\Disponibilidad;
use App\Support\WhatsApp;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReservaController extends Controller
{
    public function __construct(private Disponibilidad $disponibilidad) {}

    public function inicio(): View
    {
        return view('inicio', [
            'servicios' => Servicio::activos()->orderBy('precio')->get(),
            'barberos' => Barbero::activos()->orderBy('nombre')->get(),
        ]);
    }

    public function crear(Request $request): View
    {
        $hoy = CarbonImmutable::today();

        return view('reservar', [
            'servicios' => Servicio::activos()->orderBy('precio')->get(),
            'barberos' => Barbero::activos()->with('horarios')->orderBy('nombre')->get(),
            'fechas' => collect(range(0, config('barberia.dias_reserva')))->map(fn ($d) => $hoy->addDays($d)),
            'servicioInicial' => $request->integer('servicio') ?: null,
        ]);
    }

    public function disponibilidad(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'servicio_id' => ['required', Rule::exists('servicios', 'id')->where('activo', true)],
            'barbero_id' => ['required', Rule::exists('barberos', 'id')->where('activo', true)],
            'fecha' => ['required', 'date_format:Y-m-d'],
        ]);

        $turnos = $this->disponibilidad->turnos(
            Barbero::findOrFail($datos['barbero_id']),
            Servicio::findOrFail($datos['servicio_id']),
            CarbonImmutable::createFromFormat('Y-m-d', $datos['fecha'])->startOfDay(),
        );

        return response()->json([
            'turnos' => $turnos->map(fn (CarbonImmutable $t) => [
                'hora' => $t->format('H:i'),
                'etiqueta' => $t->format('g:i a'),
            ]),
        ]);
    }

    public function guardar(Request $request)
    {
        $request->merge(['cliente_telefono' => preg_replace('/\D/', '', (string) $request->input('cliente_telefono'))]);

        $datos = $request->validate([
            'servicio_id' => ['required', Rule::exists('servicios', 'id')->where('activo', true)],
            'barbero_id' => ['required', Rule::exists('barberos', 'id')->where('activo', true)],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
            'cliente_nombre' => ['required', 'string', 'min:3', 'max:80'],
            'cliente_telefono' => ['required', 'regex:/^9\d{8}$/'],
            'notas' => ['nullable', 'string', 'max:300'],
        ], [
            'hora.required' => 'Elige un horario.',
            'cliente_telefono.regex' => 'Ingresa un celular peruano de 9 dígitos que empiece con 9.',
        ], [
            'cliente_nombre' => 'nombre',
            'cliente_telefono' => 'celular',
        ]);

        $servicio = Servicio::findOrFail($datos['servicio_id']);
        $inicio = CarbonImmutable::createFromFormat('Y-m-d H:i', "{$datos['fecha']} {$datos['hora']}")->startOfMinute();

        $reserva = DB::transaction(function () use ($datos, $servicio, $inicio) {
            // Bloquea al barbero para que dos personas no tomen el mismo turno a la vez.
            $barbero = Barbero::whereKey($datos['barbero_id'])->lockForUpdate()->firstOrFail();

            if (! $this->disponibilidad->estaLibre($barbero, $servicio, $inicio)) {
                throw ValidationException::withMessages([
                    'hora' => 'Ese horario acaba de ocuparse. Elige otro, por favor.',
                ]);
            }

            return Reserva::create([
                'codigo' => Reserva::nuevoCodigo(),
                'servicio_id' => $servicio->id,
                'barbero_id' => $barbero->id,
                'cliente_nombre' => trim($datos['cliente_nombre']),
                'cliente_telefono' => $datos['cliente_telefono'],
                'inicia_en' => $inicio,
                'termina_en' => $inicio->addMinutes($servicio->duracion_minutos),
                'precio' => $servicio->precio,
                'estado' => EstadoReserva::Pendiente,
                'notas' => $datos['notas'] ?? null,
            ]);
        });

        // Guarda en la sesión qué reservas hizo este navegador, para que el
        // código en la URL no baste para ver datos de otra persona.
        $request->session()->push('reservas_propias', $reserva->codigo);

        return redirect()->route('reservas.ver', $reserva);
    }

    public function ver(Request $request, Reserva $reserva): View
    {
        abort_unless(in_array($reserva->codigo, $request->session()->get('reservas_propias', []), true), 404);

        $reserva->load('servicio', 'barbero');

        return view('reserva', [
            'reserva' => $reserva,
            'whatsapp' => WhatsApp::clienteAlNegocio($reserva),
        ]);
    }
}
