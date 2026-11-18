<x-layouts.admin titulo="Agenda">
    @php
        $soles = fn ($monto) => 'S/ '.number_format((float) $monto, 0);
        $filtros = request()->only('barbero', 'estado');
    @endphp

    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.agenda', ['fecha' => $fecha->subDay()->format('Y-m-d')] + $filtros) }}" class="btn-borde size-10 p-0" aria-label="Día anterior">←</a>
        <div>
            <h1 class="font-display text-2xl font-semibold leading-tight">{{ ucfirst($fecha->locale('es')->translatedFormat('l j \d\e F')) }}</h1>
            @unless ($fecha->isToday())
                <a href="{{ route('admin.agenda', $filtros) }}" class="text-sm text-laton hover:underline">Ir a hoy</a>
            @endunless
        </div>
        <a href="{{ route('admin.agenda', ['fecha' => $fecha->addDay()->format('Y-m-d')] + $filtros) }}" class="btn-borde size-10 p-0" aria-label="Día siguiente">→</a>

        <form method="GET" class="ml-auto flex flex-wrap gap-2">
            <input type="date" name="fecha" value="{{ $fecha->format('Y-m-d') }}" class="campo w-auto py-2 text-sm" onchange="this.form.submit()" aria-label="Fecha">
            <select name="barbero" class="campo w-auto py-2 text-sm" onchange="this.form.submit()" aria-label="Barbero">
                <option value="">Todos los barberos</option>
                @foreach ($barberos as $barbero)
                    <option value="{{ $barbero->id }}" @selected(request('barbero') == $barbero->id)>{{ $barbero->nombre }}</option>
                @endforeach
            </select>
            <select name="estado" class="campo w-auto py-2 text-sm" onchange="this.form.submit()" aria-label="Estado">
                <option value="">Todos los estados</option>
                @foreach ($estados as $estado)
                    <option value="{{ $estado->value }}" @selected(request('estado') === $estado->value)>{{ $estado->etiqueta() }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <dl class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            ['Citas del día', $resumen['citas']],
            ['Pendientes por confirmar', $resumen['pendientes']],
            ['Ingresos del día', $soles($resumen['ingresos_dia'])],
            ['Ingresos de la semana', $soles($resumen['ingresos_semana'])],
        ] as [$texto, $valor])
            <div class="tarjeta p-4">
                <dt class="text-xs text-stone-600 sm:text-sm">{{ $texto }}</dt>
                <dd class="mt-1 font-display text-2xl font-semibold tabular-nums sm:text-3xl">{{ $valor }}</dd>
            </div>
        @endforeach
    </dl>
    <p class="mt-2 text-xs text-stone-500">Los ingresos cuentan citas confirmadas y completadas.</p>

    <section class="mt-6">
        @forelse ($reservas as $reserva)
            <article @class(['tarjeta mb-3 flex flex-col gap-4 p-4 sm:flex-row sm:items-center', 'opacity-60' => $reserva->estado === \App\Enums\EstadoReserva::Cancelada])>
                <div class="flex items-center gap-4 sm:w-32 sm:flex-col sm:items-start sm:gap-0">
                    <p class="font-display text-2xl font-semibold tabular-nums">{{ $reserva->inicia_en->format('H:i') }}</p>
                    <p class="text-xs text-stone-500">hasta {{ $reserva->termina_en->format('H:i') }}</p>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-semibold">{{ $reserva->cliente_nombre }}</p>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $reserva->estado->clases() }}">{{ $reserva->estado->etiqueta() }}</span>
                        <span class="font-mono text-xs text-stone-500">{{ $reserva->codigo }}</span>
                    </div>
                    <p class="text-sm text-stone-600">{{ $reserva->servicio->nombre }} · {{ $reserva->barbero->nombre }} · S/ {{ number_format($reserva->precio, 0) }}</p>
                    <p class="text-sm text-stone-600">{{ $reserva->cliente_telefono }}</p>
                    @if ($reserva->notas)
                        <p class="mt-1 text-sm italic text-stone-700">“{{ $reserva->notas }}”</p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ \App\Support\WhatsApp::negocioAlCliente($reserva) }}" target="_blank" rel="noopener" class="btn-wsp px-4 py-2" title="Abrir WhatsApp con el mensaje listo">WhatsApp</a>
                    @foreach ([
                        'confirmada' => ['Confirmar', 'btn-primario'],
                        'completada' => ['Atendida', 'btn-borde'],
                        'cancelada' => ['Cancelar', 'btn-borde text-rose-700'],
                    ] as $valor => [$texto, $clase])
                        @if ($reserva->estado->value !== $valor && ! ($valor === 'confirmada' && $reserva->estado !== \App\Enums\EstadoReserva::Pendiente))
                            <form method="POST" action="{{ route('admin.reservas.estado', $reserva) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="estado" value="{{ $valor }}">
                                <button class="{{ $clase }} px-4 py-2" @if ($valor === 'cancelada') onclick="return confirm({{ \Illuminate\Support\Js::from('¿Cancelar la cita de '.$reserva->cliente_nombre.'? El horario quedará libre.') }})" @endif>{{ $texto }}</button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </article>
        @empty
            <div class="tarjeta p-10 text-center text-stone-600">
                <p class="font-display text-xl font-semibold text-tinta">Sin citas</p>
                <p class="mt-1 text-sm">No hay reservas para este día con esos filtros.</p>
            </div>
        @endforelse
    </section>
</x-layouts.admin>
