<?php

namespace App\Models;

use App\Enums\EstadoReserva;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'codigo', 'servicio_id', 'barbero_id', 'cliente_nombre', 'cliente_telefono',
        'inicia_en', 'termina_en', 'precio', 'estado', 'notas',
    ];

    protected function casts(): array
    {
        return [
            'inicia_en' => 'datetime',
            'termina_en' => 'datetime',
            'precio' => 'decimal:2',
            'estado' => EstadoReserva::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'codigo';
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function barbero(): BelongsTo
    {
        return $this->belongsTo(Barbero::class);
    }

    public function scopeOcupanHorario(Builder $query): Builder
    {
        return $query->whereIn('estado', EstadoReserva::ocupanHorario());
    }

    public static function nuevoCodigo(): string
    {
        // Sin caracteres que se confunden (0/O, 1/I) para poder dictarlo por teléfono.
        $alfabeto = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

        do {
            $codigo = '';
            for ($i = 0; $i < 6; $i++) {
                $codigo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
            }
        } while (static::where('codigo', $codigo)->exists());

        return $codigo;
    }

    /** Teléfono con código de país, listo para un enlace wa.me. */
    public function telefonoWhatsapp(): string
    {
        return '51'.preg_replace('/\D/', '', $this->cliente_telefono);
    }
}
