<?php

namespace Tests\Feature;

use App\Enums\EstadoReserva;
use App\Models\Barbero;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Models\User;
use App\Services\Disponibilidad;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaTest extends TestCase
{
    use RefreshDatabase;

    private Servicio $corte;

    private Servicio $corteBarba;

    private Barbero $barbero;

    protected function setUp(): void
    {
        parent::setUp();

        // Martes 17 de noviembre de 2026, 8:00 a.m. en Lima.
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-17 08:00', 'America/Lima'));
        \Illuminate\Support\Carbon::setTestNow(CarbonImmutable::parse('2026-11-17 08:00', 'America/Lima'));

        $this->corte = Servicio::create(['nombre' => 'Corte', 'duracion_minutos' => 30, 'precio' => 25]);
        $this->corteBarba = Servicio::create(['nombre' => 'Corte + barba', 'duracion_minutos' => 60, 'precio' => 40]);
        $this->barbero = Barbero::create(['nombre' => 'Luis Quispe']);
        $this->barbero->horarios()->createMany([
            ['dia_semana' => 2, 'hora_inicio' => '09:00', 'hora_fin' => '13:00'],
            ['dia_semana' => 2, 'hora_inicio' => '14:00', 'hora_fin' => '16:00'],
        ]);
    }

    private function turnos(Servicio $servicio, string $fecha = '2026-11-17'): array
    {
        return app(Disponibilidad::class)
            ->turnos($this->barbero, $servicio, CarbonImmutable::parse($fecha))
            ->map->format('H:i')->all();
    }

    private function reservar(array $datos = []): \Illuminate\Testing\TestResponse
    {
        return $this->post('/reservar', $datos + [
            'servicio_id' => $this->corte->id,
            'barbero_id' => $this->barbero->id,
            'fecha' => '2026-11-17',
            'hora' => '10:00',
            'cliente_nombre' => 'Carlos Mendoza',
            'cliente_telefono' => '987 654 321',
        ]);
    }

    public function test_los_turnos_respetan_bloques_y_duracion(): void
    {
        $this->assertSame(
            ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '14:00', '14:30', '15:00', '15:30'],
            $this->turnos($this->corte),
        );

        // Un servicio de 60 min no puede empezar a las 12:30 porque el bloque termina a las 13:00.
        $this->assertNotContains('12:30', $this->turnos($this->corteBarba));
        $this->assertContains('12:00', $this->turnos($this->corteBarba));
    }

    public function test_no_ofrece_turnos_pasados_ni_dentro_de_la_anticipacion(): void
    {
        \Illuminate\Support\Carbon::setTestNow(CarbonImmutable::parse('2026-11-17 10:10', 'America/Lima'));
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-17 10:10', 'America/Lima'));

        // Anticipación de 60 min: lo primero disponible es 11:30.
        $this->assertSame('11:30', $this->turnos($this->corte)[0]);
    }

    public function test_dia_sin_horario_no_tiene_turnos(): void
    {
        $this->assertSame([], $this->turnos($this->corte, '2026-11-18')); // miércoles
    }

    public function test_una_reserva_bloquea_los_turnos_que_se_cruzan(): void
    {
        $this->reservar(['servicio_id' => $this->corteBarba->id, 'hora' => '10:00'])->assertRedirect();

        $turnos = $this->turnos($this->corte);
        $this->assertNotContains('10:00', $turnos);
        $this->assertNotContains('10:30', $turnos);
        $this->assertContains('11:00', $turnos);
        // Un servicio de 60 min a las 9:30 chocaría con la reserva de las 10:00.
        $this->assertNotContains('09:30', $this->turnos($this->corteBarba));
    }

    public function test_reserva_valida_se_guarda_pendiente_y_muestra_confirmacion(): void
    {
        $respuesta = $this->reservar();

        $reserva = Reserva::sole();
        $respuesta->assertRedirect("/reserva/{$reserva->codigo}");
        $this->assertSame(EstadoReserva::Pendiente, $reserva->estado);
        $this->assertSame('987654321', $reserva->cliente_telefono);
        $this->assertSame('10:30', $reserva->termina_en->format('H:i'));

        $this->get("/reserva/{$reserva->codigo}")
            ->assertOk()
            ->assertSee($reserva->codigo)
            ->assertSee('https://wa.me/', false);
    }

    public function test_no_permite_reservar_dos_veces_el_mismo_turno(): void
    {
        $this->reservar()->assertRedirect();
        $this->reservar(['cliente_nombre' => 'Otro Cliente'])->assertSessionHasErrors('hora');

        $this->assertSame(1, Reserva::count());
    }

    public function test_una_reserva_cancelada_libera_el_turno(): void
    {
        $this->reservar();
        Reserva::sole()->update(['estado' => EstadoReserva::Cancelada]);

        $this->assertContains('10:00', $this->turnos($this->corte));
    }

    public function test_valida_celular_peruano(): void
    {
        $this->reservar(['cliente_telefono' => '12345'])->assertSessionHasErrors('cliente_telefono');
        $this->assertSame(0, Reserva::count());
    }

    public function test_otra_persona_no_puede_ver_la_reserva_con_solo_el_codigo(): void
    {
        $this->reservar();
        $codigo = Reserva::sole()->codigo;

        $this->flushSession();
        $this->get("/reserva/{$codigo}")->assertNotFound();
    }

    public function test_endpoint_de_disponibilidad(): void
    {
        $this->getJson('/disponibilidad?'.http_build_query([
            'servicio_id' => $this->corte->id,
            'barbero_id' => $this->barbero->id,
            'fecha' => '2026-11-17',
        ]))->assertOk()->assertJsonPath('turnos.0', ['hora' => '09:00', 'etiqueta' => '9:00 am']);
    }

    public function test_panel_requiere_login_y_permite_cambiar_estado(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');

        $this->reservar();
        $reserva = Reserva::sole();

        $this->actingAs(User::factory()->create())
            ->get('/admin?fecha=2026-11-17')
            ->assertOk()
            ->assertSee('Carlos Mendoza');

        $this->patch("/admin/reservas/{$reserva->codigo}/estado", ['estado' => 'confirmada'])->assertRedirect();
        $this->assertSame(EstadoReserva::Confirmada, $reserva->fresh()->estado);
    }

    public function test_paginas_publicas_cargan(): void
    {
        $this->get('/')->assertOk()->assertSee('Corte + barba');
        $this->get('/reservar')->assertOk()->assertSee('Luis Quispe');
    }
}
