<?php

namespace Database\Seeders;

use App\Enums\EstadoReserva;
use App\Models\Barbero;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\User;
use App\Services\Disponibilidad;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * Datos de ejemplo de la demo. Se puede volver a ejecutar sin duplicar nada.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@navaja.test')],
            ['name' => 'Administrador', 'password' => env('ADMIN_PASSWORD', 'navaja-demo-2026')],
        );

        $servicios = collect([
            ['Corte clásico', 'Tijera y máquina, lavado y peinado.', 30, 25],
            ['Corte + barba', 'Corte completo y perfilado de barba con navaja.', 60, 40],
            ['Arreglo de barba', 'Perfilado, toalla caliente y aceite.', 30, 18],
            ['Fade / degradado', 'Degradado a detalle con diseño opcional.', 45, 30],
            ['Corte niño', 'Hasta 12 años.', 30, 18],
        ])->map(fn ($s) => Servicio::updateOrCreate(
            ['nombre' => $s[0]],
            ['descripcion' => $s[1], 'duracion_minutos' => $s[2], 'precio' => $s[3], 'activo' => true],
        ));

        $barberos = collect([
            ['Luis Quispe', 'Fades y diseños'],
            ['Diego Huamán', 'Barba y navaja'],
            ['Andrea Rojas', 'Cortes clásicos'],
        ])->map(fn ($b) => Barbero::updateOrCreate(
            ['nombre' => $b[0]],
            ['especialidad' => $b[1], 'activo' => true],
        ));

        // Lunes a sábado, 9:00–13:00 y 14:00–20:00. Domingo cerrado.
        foreach ($barberos as $barbero) {
            if ($barbero->horarios()->exists()) {
                continue;
            }
            foreach (range(1, 6) as $dia) {
                $barbero->horarios()->createMany([
                    ['dia_semana' => $dia, 'hora_inicio' => '09:00', 'hora_fin' => '13:00'],
                    ['dia_semana' => $dia, 'hora_inicio' => '14:00', 'hora_fin' => '20:00'],
                ]);
            }
        }

        if (Reserva::exists()) {
            return;
        }

        // Algunas citas para que el panel no se vea vacío.
        $clientes = ['Carlos Mendoza', 'Jorge Paredes', 'Renato Salazar', 'Miguel Torres', 'Kevin Castro', 'Óscar Lima', 'Bruno Vargas', 'Piero Ramos'];
        $disponibilidad = app(Disponibilidad::class);
        $hoy = CarbonImmutable::today();
        $i = 0;

        foreach ([0, 1, 2] as $offset) {
            $fecha = $hoy->addDays($offset);
            foreach ($barberos as $barbero) {
                $servicio = $servicios[$i % $servicios->count()];
                $turnos = $disponibilidad->turnos($barbero, $servicio, $fecha);
                foreach ($turnos->filter(fn ($t, $k) => $k % 5 === 1)->take(2) as $inicio) {
                    Reserva::create([
                        'codigo' => Reserva::nuevoCodigo(),
                        'servicio_id' => $servicio->id,
                        'barbero_id' => $barbero->id,
                        'cliente_nombre' => $clientes[$i % count($clientes)],
                        'cliente_telefono' => '9'.str_pad((string) (10000000 + $i * 7919), 8, '0', STR_PAD_LEFT),
                        'inicia_en' => $inicio,
                        'termina_en' => $inicio->addMinutes($servicio->duracion_minutos),
                        'precio' => $servicio->precio,
                        'estado' => $i % 3 === 0 ? EstadoReserva::Pendiente : EstadoReserva::Confirmada,
                    ]);
                    $i++;
                }
            }
        }
    }
}
