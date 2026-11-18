<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoReserva;
use App\Http\Controllers\Controller;
use App\Models\Barbero;
use App\Models\Reserva;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgendaController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'fecha' => ['nullable', 'date_format:Y-m-d'],
            'barbero' => ['nullable', 'integer'],
            'estado' => ['nullable', Rule::enum(EstadoReserva::class)],
        ]);

        $fecha = $request->filled('fecha')
            ? CarbonImmutable::createFromFormat('Y-m-d', $request->string('fecha'))->startOfDay()
            : CarbonImmutable::today();

        $reservas = Reserva::with('servicio', 'barbero')
            ->whereBetween('inicia_en', [$fecha, $fecha->endOfDay()])
            ->when($request->filled('barbero'), fn ($q) => $q->where('barbero_id', $request->integer('barbero')))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
            ->orderBy('inicia_en')
            ->get();

        $semana = [$fecha->startOfWeek(), $fecha->endOfWeek()];

        return view('admin.agenda', [
            'fecha' => $fecha,
            'reservas' => $reservas,
            'barberos' => Barbero::orderBy('nombre')->get(),
            'estados' => EstadoReserva::cases(),
            'resumen' => [
                'citas' => Reserva::ocupanHorario()->whereBetween('inicia_en', [$fecha, $fecha->endOfDay()])->count(),
                'pendientes' => Reserva::where('estado', EstadoReserva::Pendiente)
                    ->where('inicia_en', '>=', now())->count(),
                'ingresos_dia' => Reserva::whereIn('estado', [EstadoReserva::Confirmada, EstadoReserva::Completada])
                    ->whereBetween('inicia_en', [$fecha, $fecha->endOfDay()])->sum('precio'),
                'ingresos_semana' => Reserva::whereIn('estado', [EstadoReserva::Confirmada, EstadoReserva::Completada])
                    ->whereBetween('inicia_en', $semana)->sum('precio'),
            ],
        ]);
    }

    public function estado(Request $request, Reserva $reserva)
    {
        $datos = $request->validate(['estado' => ['required', Rule::enum(EstadoReserva::class)]]);

        $reserva->update(['estado' => $datos['estado']]);

        return back()->with('ok', "Reserva {$reserva->codigo}: {$reserva->estado->etiqueta()}.");
    }
}
