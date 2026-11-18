<x-layouts.publico titulo="Reserva {{ $reserva->codigo }}">
    <section class="mx-auto max-w-lg px-4 py-10">
        <div class="tarjeta overflow-hidden">
            <div class="bg-tinta px-6 py-8 text-center text-crema">
                <span class="mx-auto grid size-12 place-items-center rounded-full bg-laton text-2xl text-white" aria-hidden="true">✓</span>
                <h1 class="mt-4 font-display text-3xl font-semibold">¡Reserva registrada!</h1>
                <p class="mt-2 text-crema/75">Tu código es</p>
                <p class="mt-1 font-mono text-3xl font-bold tracking-[.3em] text-laton-claro">{{ $reserva->codigo }}</p>
            </div>

            <dl class="divide-y divide-stone-200 px-6 text-sm">
                @foreach ([
                    'Servicio' => $reserva->servicio->nombre,
                    'Barbero' => $reserva->barbero->nombre,
                    'Fecha' => ucfirst($reserva->inicia_en->locale('es')->translatedFormat('l j \d\e F')),
                    'Hora' => $reserva->inicia_en->format('g:i a').' – '.$reserva->termina_en->format('g:i a'),
                    'Total' => 'S/ '.number_format($reserva->precio, 2),
                    'A nombre de' => $reserva->cliente_nombre,
                ] as $dato => $valor)
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-stone-600">{{ $dato }}</dt>
                        <dd class="text-right font-medium">{{ $valor }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="space-y-3 bg-crema px-6 py-6">
                <p class="text-sm text-stone-700">Tu cita queda <strong>pendiente</strong> hasta que la confirmemos. Envíanos el mensaje por WhatsApp para confirmarla al instante.</p>
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn-wsp w-full text-base">
                    <svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.3.5-.4.4c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.4 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3.1.2.1.7-.1 1.1Z"/></svg>
                    Confirmar por WhatsApp
                </a>
                <a href="{{ route('inicio') }}" class="btn-borde w-full">Volver al inicio</a>
            </div>
        </div>
        <p class="mt-4 text-center text-xs text-stone-500">Guarda tu código. Si necesitas cambiar la hora, escríbenos por WhatsApp.</p>
    </section>
</x-layouts.publico>
