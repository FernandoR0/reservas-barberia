<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $titulo ?? 'Panel' }} · {{ config('barberia.nombre') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-stone-100">
    <header class="sticky top-0 z-20 bg-tinta text-crema">
        <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3">
            <a href="{{ route('admin.agenda') }}" class="font-display text-lg font-semibold">{{ config('barberia.nombre') }} <span class="text-sm font-normal text-crema/60">· Panel</span></a>
            <nav class="ml-auto flex items-center gap-1 overflow-x-auto text-sm">
                @foreach (['admin.agenda' => 'Agenda', 'admin.servicios' => 'Servicios', 'admin.barberos' => 'Barberos'] as $ruta => $texto)
                    <a href="{{ route($ruta) }}" @class(['rounded-full px-3 py-1.5 whitespace-nowrap', 'bg-crema/15 text-crema' => request()->routeIs($ruta), 'text-crema/70 hover:text-crema' => ! request()->routeIs($ruta)])>{{ $texto }}</a>
                @endforeach
                <a href="{{ route('inicio') }}" target="_blank" class="hidden rounded-full px-3 py-1.5 text-crema/70 hover:text-crema sm:inline">Ver sitio ↗</a>
                <form method="POST" action="{{ route('admin.salir') }}">
                    @csrf
                    <button class="rounded-full px-3 py-1.5 text-crema/70 hover:text-crema">Salir</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-6">
        @if (session('ok'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">{{ session('ok') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
