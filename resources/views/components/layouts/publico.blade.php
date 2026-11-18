<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#17140f">
    <title>{{ isset($titulo) ? $titulo.' · ' : '' }}{{ config('barberia.nombre') }}</title>
    <meta name="description" content="Reserva tu corte en {{ config('barberia.nombre') }}, {{ config('barberia.ciudad') }}. Elige servicio, barbero y horario en menos de un minuto.">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh flex flex-col">
    <div class="bg-laton px-4 py-1.5 text-center text-xs font-medium text-white">
        Proyecto demostrativo · los datos son ficticios
    </div>

    <header class="bg-tinta text-crema">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
            <a href="{{ route('inicio') }}" class="flex items-center gap-2.5">
                <svg viewBox="0 0 32 32" class="size-8" aria-hidden="true">
                    <circle cx="16" cy="16" r="15" fill="none" stroke="currentColor" stroke-width="1.5" />
                    <path d="M9 21 22 8l2 2-13 13z" fill="#d9b26a" />
                    <path d="m11 23-2.5 2.5M22 8l2-2" stroke="currentColor" stroke-width="1.5" />
                </svg>
                <span class="font-display text-xl font-semibold tracking-tight">{{ config('barberia.nombre') }}</span>
            </a>
            <nav class="flex items-center gap-5 text-sm">
                <a href="{{ route('inicio') }}#servicios" class="hidden text-crema/80 hover:text-crema sm:inline">Servicios</a>
                <a href="{{ route('inicio') }}#equipo" class="hidden text-crema/80 hover:text-crema sm:inline">Equipo</a>
                <a href="{{ route('reservas.crear') }}" class="rounded-full bg-laton px-4 py-2 font-semibold text-white hover:bg-[#9a6c22]">Reservar</a>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="bg-tinta text-crema/70">
        <div class="mx-auto flex max-w-5xl flex-col gap-2 px-4 py-8 text-sm sm:flex-row sm:justify-between">
            <p>{{ config('barberia.direccion') }} · Lun a sáb, 9:00 a 20:00</p>
            <p>Demo de sistema de reservas · Laravel + MySQL</p>
        </div>
    </footer>
</body>
</html>
