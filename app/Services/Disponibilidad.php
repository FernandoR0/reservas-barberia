<?php

namespace App\Services;

use App\Models\Barbero;
use App\Models\Reserva;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Calcula los turnos libres de un barbero para un servicio en una fecha.
 *
 * Un turno es válido si:
 *  - cae dentro de un bloque de trabajo del barbero ese día de la semana,
 *  - el servicio completo termina antes de que acabe el bloque,
 *  - respeta la anticipación mínima,
 *  - no se cruza con otra reserva pendiente o confirmada del mismo barbero.
 */
class Disponibilidad
{
    /**
     * @return Collection<int, CarbonImmutable> horas de inicio disponibles
     */
    public function turnos(Barbero $barbero, Servicio $servicio, CarbonImmutable $fecha, ?int $ignorarReservaId = null): Collection
    {
        $zona = config('app.timezone');
        $fecha = $fecha->setTimezone($zona)->startOfDay();
        $intervalo = config('barberia.intervalo_minutos');
        $minimo = CarbonImmutable::now($zona)->addMinutes(config('barberia.anticipacion_minutos'));

        if (! $this->fechaReservable($fecha)) {
            return collect();
        }

        $bloques = $barbero->horarios()->where('dia_semana', $fecha->dayOfWeek)->get();

        $ocupados = Reserva::query()
            ->where('barbero_id', $barbero->id)
            ->ocupanHorario()
            ->when($ignorarReservaId, fn ($q) => $q->where('id', '!=', $ignorarReservaId))
            ->where('inicia_en', '<', $fecha->endOfDay())
            ->where('termina_en', '>', $fecha)
            ->get(['inicia_en', 'termina_en']);

        $turnos = collect();

        foreach ($bloques as $bloque) {
            $inicio = $fecha->setTimeFromTimeString($bloque->hora_inicio);
            $fin = $fecha->setTimeFromTimeString($bloque->hora_fin);

            for ($t = $inicio; $t->addMinutes($servicio->duracion_minutos) <= $fin; $t = $t->addMinutes($intervalo)) {
                $termina = $t->addMinutes($servicio->duracion_minutos);

                if ($t < $minimo) {
                    continue;
                }

                $choca = $ocupados->contains(
                    fn (Reserva $r) => $t < $r->termina_en && $termina > $r->inicia_en
                );

                if (! $choca) {
                    $turnos->push($t);
                }
            }
        }

        return $turnos->sort()->values();
    }

    public function estaLibre(Barbero $barbero, Servicio $servicio, CarbonImmutable $inicio): bool
    {
        return $this->turnos($barbero, $servicio, $inicio->startOfDay())
            ->contains(fn (CarbonImmutable $t) => $t->equalTo($inicio));
    }

    public function fechaReservable(CarbonImmutable $fecha): bool
    {
        $hoy = CarbonImmutable::today(config('app.timezone'));

        return $fecha->betweenIncluded($hoy, $hoy->addDays(config('barberia.dias_reserva')));
    }
}
