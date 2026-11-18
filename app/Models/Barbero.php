<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Barbero extends Model
{
    protected $table = 'barberos';

    protected $fillable = ['nombre', 'especialidad', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class)->orderBy('dia_semana')->orderBy('hora_inicio');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function iniciales(): string
    {
        return Str::of($this->nombre)->explode(' ')->take(2)
            ->map(fn ($parte) => Str::upper(Str::substr($parte, 0, 1)))->implode('');
    }
}
