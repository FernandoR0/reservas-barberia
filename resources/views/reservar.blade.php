<x-layouts.publico titulo="Reservar">
    <form method="POST" action="{{ route('reservas.guardar') }}" id="form-reserva"
          data-url-disponibilidad="{{ route('reservas.disponibilidad') }}"
          data-hora-anterior="{{ old('hora') }}"
          class="mx-auto max-w-3xl px-4 pb-40 pt-8">
        @csrf

        <h1 class="font-display text-3xl font-semibold sm:text-4xl">Reserva tu cita</h1>
        <p class="mt-1 text-stone-600">Cuatro pasos y listo.</p>

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900" role="alert">
                <p class="font-semibold">Revisa estos datos:</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 1. Servicio --}}
        <fieldset class="mt-10">
            <legend class="paso"><span>1</span> Servicio</legend>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($servicios as $servicio)
                    <label class="opcion">
                        <input type="radio" name="servicio_id" value="{{ $servicio->id }}" class="sr-only"
                               data-nombre="{{ $servicio->nombre }}" data-precio="{{ number_format($servicio->precio, 0) }}"
                               data-duracion="{{ $servicio->duracion_minutos }}"
                               @checked((int) old('servicio_id', $servicioInicial) === $servicio->id)>
                        <span class="flex items-start justify-between gap-3">
                            <span>
                                <span class="block font-semibold">{{ $servicio->nombre }}</span>
                                <span class="block text-sm text-stone-600">{{ $servicio->duracion_minutos }} min</span>
                            </span>
                            <span class="font-display text-lg font-semibold">S/ {{ number_format($servicio->precio, 0) }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        {{-- 2. Barbero --}}
        <fieldset class="mt-10">
            <legend class="paso"><span>2</span> Barbero</legend>
            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                @foreach ($barberos as $barbero)
                    <label class="opcion flex items-center gap-3">
                        <input type="radio" name="barbero_id" value="{{ $barbero->id }}" class="sr-only"
                               data-nombre="{{ $barbero->nombre }}"
                               data-dias="{{ $barbero->horarios->pluck('dia_semana')->unique()->values()->toJson() }}"
                               @checked((int) old('barbero_id') === $barbero->id)>
                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-tinta font-display font-semibold text-laton-claro">{{ $barbero->iniciales() }}</span>
                        <span>
                            <span class="block font-semibold leading-tight">{{ $barbero->nombre }}</span>
                            <span class="block text-sm text-stone-600">{{ $barbero->especialidad }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        {{-- 3. Fecha y hora --}}
        <fieldset class="mt-10">
            <legend class="paso"><span>3</span> Día y hora</legend>
            <div class="-mx-4 mt-4 flex snap-x gap-2 overflow-x-auto px-4 pb-2" id="fechas">
                @foreach ($fechas as $fecha)
                    <label class="snap-start">
                        <input type="radio" name="fecha" value="{{ $fecha->format('Y-m-d') }}" class="peer sr-only"
                               data-dia="{{ $fecha->dayOfWeek }}"
                               data-etiqueta="{{ $fecha->locale('es')->translatedFormat('l j \d\e F') }}"
                               @checked(old('fecha') === $fecha->format('Y-m-d'))>
                        <span class="flex w-16 cursor-pointer flex-col items-center rounded-2xl border border-stone-200 bg-white py-3 transition hover:border-stone-400 peer-checked:border-tinta peer-checked:bg-tinta peer-checked:text-crema peer-disabled:cursor-not-allowed peer-disabled:opacity-35">
                            <span class="text-xs uppercase">{{ $loop->first ? 'Hoy' : $fecha->locale('es')->translatedFormat('D') }}</span>
                            <span class="font-display text-2xl font-semibold">{{ $fecha->day }}</span>
                            <span class="text-xs">{{ $fecha->locale('es')->translatedFormat('M') }}</span>
                        </span>
                    </label>
                @endforeach
            </div>

            <div id="turnos" class="mt-5" aria-live="polite">
                <p class="rounded-xl bg-crema p-4 text-sm text-stone-600">Elige servicio, barbero y día para ver los horarios libres.</p>
            </div>
        </fieldset>

        {{-- 4. Datos --}}
        <fieldset class="mt-10">
            <legend class="paso"><span>4</span> Tus datos</legend>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="cliente_nombre" class="etiqueta">Nombre y apellido</label>
                    <input id="cliente_nombre" name="cliente_nombre" value="{{ old('cliente_nombre') }}" required minlength="3" maxlength="80" autocomplete="name" class="campo">
                </div>
                <div>
                    <label for="cliente_telefono" class="etiqueta">Celular (WhatsApp)</label>
                    <div class="flex">
                        <span class="grid place-items-center rounded-l-xl border border-r-0 border-stone-300 bg-crema px-3 text-stone-600">+51</span>
                        <input id="cliente_telefono" name="cliente_telefono" value="{{ old('cliente_telefono') }}" required inputmode="numeric" autocomplete="tel-national"
                               pattern="9[0-9 ]{8,10}" placeholder="987 654 321" class="campo rounded-l-none">
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <label for="notas" class="etiqueta">Comentario <span class="font-normal text-stone-500">(opcional)</span></label>
                    <textarea id="notas" name="notas" rows="2" maxlength="300" placeholder="Ej.: quiero el mismo corte de la última vez" class="campo">{{ old('notas') }}</textarea>
                </div>
            </div>
        </fieldset>

        {{-- Resumen fijo abajo --}}
        <div class="fixed inset-x-0 bottom-0 z-10 border-t border-stone-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-3xl items-center gap-4 px-4 py-3">
                <div class="min-w-0 flex-1 text-sm">
                    <p id="resumen" class="truncate font-medium">Aún no eliges un horario</p>
                    <p id="resumen-detalle" class="truncate text-stone-600"></p>
                </div>
                <button type="submit" id="btn-reservar" class="btn-laton shrink-0" disabled>Reservar</button>
            </div>
        </div>
    </form>

    @vite('resources/js/reservar.js')
</x-layouts.publico>
