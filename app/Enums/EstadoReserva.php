<?php

namespace App\Enums;

enum EstadoReserva: string
{
    case Pendiente = 'pendiente';
    case Confirmada = 'confirmada';
    case Completada = 'completada';
    case Cancelada = 'cancelada';

    public function etiqueta(): string
    {
        return ucfirst($this->value);
    }

    /** Clases de Tailwind para la etiqueta del estado. */
    public function clases(): string
    {
        return match ($this) {
            self::Pendiente => 'bg-amber-100 text-amber-900',
            self::Confirmada => 'bg-emerald-100 text-emerald-900',
            self::Completada => 'bg-stone-200 text-stone-700',
            self::Cancelada => 'bg-rose-100 text-rose-900',
        };
    }

    /** Estados que ocupan el horario del barbero. */
    public static function ocupanHorario(): array
    {
        return [self::Pendiente->value, self::Confirmada->value];
    }
}
