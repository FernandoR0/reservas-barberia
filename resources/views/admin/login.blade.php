<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Ingresar · {{ config('barberia.nombre') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-dvh place-items-center bg-tinta px-4">
    <main class="w-full max-w-sm">
        <h1 class="mb-6 text-center font-display text-3xl font-semibold text-crema">{{ config('barberia.nombre') }}</h1>
        <form method="POST" action="{{ route('login') }}" class="tarjeta space-y-4 p-6">
            @csrf
            <h2 class="text-lg font-semibold">Panel de administración</h2>

            @error('email')
                <p class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-900" role="alert">{{ $message }}</p>
            @enderror

            <div>
                <label for="email" class="etiqueta">Correo</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="campo">
            </div>
            <div>
                <label for="password" class="etiqueta">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="current-password" class="campo">
            </div>
            <label class="flex items-center gap-2 text-sm text-stone-700">
                <input type="checkbox" name="recordar" class="size-4 accent-tinta"> Recordarme
            </label>
            <button class="btn-primario w-full">Ingresar</button>
            <p class="text-center text-xs text-stone-500">Demo: las credenciales están en el README.</p>
        </form>
        <p class="mt-6 text-center text-sm"><a href="{{ route('inicio') }}" class="text-crema/70 hover:text-crema">← Volver al sitio</a></p>
    </main>
</body>
</html>
