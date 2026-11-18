<x-layouts.publico>
    <section class="bg-tinta text-crema">
        <div class="mx-auto grid max-w-5xl gap-10 px-4 pb-16 pt-10 md:grid-cols-[1.2fr_1fr] md:items-center md:pb-24 md:pt-16">
            <div>
                <p class="mb-4 text-sm font-semibold uppercase tracking-[.2em] text-laton-claro">Barbería en {{ config('barberia.ciudad') }}</p>
                <h1 class="font-display text-4xl font-semibold leading-[1.05] sm:text-6xl">Tu corte, a tu hora. Sin esperar turno.</h1>
                <p class="mt-5 max-w-md text-lg text-crema/75">Elige el servicio, tu barbero y un horario libre. Reservas en menos de un minuto y te confirmamos por WhatsApp.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('reservas.crear') }}" class="btn-laton px-7 text-base">Reservar ahora</a>
                    <a href="#servicios" class="btn border border-crema/25 text-crema hover:border-crema">Ver precios</a>
                </div>
            </div>
            <dl class="grid grid-cols-3 gap-3 md:grid-cols-1">
                @foreach ([[$barberos->count(), 'barberos'], ['Lun–Sáb', '9:00 a 20:00'], ['S/ '.number_format($servicios->min('precio'), 0), 'desde']] as [$valor, $texto])
                    <div class="rounded-2xl border border-crema/15 p-4 md:p-5">
                        <dt class="text-xs text-crema/60 md:text-sm">{{ $texto }}</dt>
                        <dd class="font-display text-xl font-semibold md:text-3xl">{{ $valor }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <section id="servicios" class="mx-auto max-w-5xl scroll-mt-4 px-4 py-14">
        <h2 class="font-display text-3xl font-semibold">Servicios y precios</h2>
        <p class="mt-2 text-stone-600">Precios fijos, sin sorpresas.</p>
        <ul class="mt-8 divide-y divide-stone-200 border-y border-stone-200">
            @foreach ($servicios as $servicio)
                <li>
                    <a href="{{ route('reservas.crear', ['servicio' => $servicio->id]) }}" class="group flex items-center gap-4 py-5">
                        <div class="flex-1">
                            <p class="font-semibold group-hover:text-laton">{{ $servicio->nombre }}</p>
                            <p class="text-sm text-stone-600">{{ $servicio->descripcion }} · {{ $servicio->duracion_minutos }} min</p>
                        </div>
                        <p class="font-display text-xl font-semibold">S/ {{ number_format($servicio->precio, 0) }}</p>
                        <span class="text-stone-400 transition group-hover:translate-x-1 group-hover:text-laton" aria-hidden="true">→</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <section id="equipo" class="bg-crema">
        <div class="mx-auto max-w-5xl scroll-mt-4 px-4 py-14">
            <h2 class="font-display text-3xl font-semibold">El equipo</h2>
            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                @foreach ($barberos as $barbero)
                    <div class="tarjeta flex items-center gap-4 p-5">
                        <span class="grid size-14 shrink-0 place-items-center rounded-full bg-tinta font-display text-lg font-semibold text-laton-claro">{{ $barbero->iniciales() }}</span>
                        <div>
                            <p class="font-semibold">{{ $barbero->nombre }}</p>
                            <p class="text-sm text-stone-600">{{ $barbero->especialidad }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 py-14">
        <h2 class="font-display text-3xl font-semibold">Cómo funciona</h2>
        <ol class="mt-8 grid gap-6 sm:grid-cols-3">
            @foreach (['Elige el servicio y tu barbero.', 'Escoge un horario libre del calendario.', 'Recibe tu código y confírmalo por WhatsApp.'] as $n => $paso)
                <li class="flex gap-4">
                    <span class="font-display text-4xl font-semibold text-laton">{{ $n + 1 }}</span>
                    <p class="pt-2 text-stone-700">{{ $paso }}</p>
                </li>
            @endforeach
        </ol>
        <a href="{{ route('reservas.crear') }}" class="btn-primario mt-10 px-7">Reservar mi cita</a>
    </section>
</x-layouts.publico>
