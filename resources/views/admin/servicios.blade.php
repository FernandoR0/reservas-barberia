<x-layouts.admin titulo="Servicios">
    <h1 class="font-display text-2xl font-semibold">Servicios</h1>
    <p class="text-sm text-stone-600">Lo que cambies aquí se ve al instante en el sitio y en los horarios disponibles.</p>

    <div class="mt-6 space-y-3">
        @foreach ($servicios as $servicio)
            <form method="POST" action="{{ route('admin.servicios.actualizar', $servicio) }}" class="tarjeta grid gap-3 p-4 sm:grid-cols-[2fr_3fr_auto_auto_auto_auto] sm:items-end">
                @csrf
                @method('PUT')
                <div>
                    <label class="etiqueta text-xs">Nombre</label>
                    <input name="nombre" value="{{ $servicio->nombre }}" required maxlength="80" class="campo py-2">
                </div>
                <div>
                    <label class="etiqueta text-xs">Descripción</label>
                    <input name="descripcion" value="{{ $servicio->descripcion }}" maxlength="255" class="campo py-2">
                </div>
                <div>
                    <label class="etiqueta text-xs">Minutos</label>
                    <input type="number" name="duracion_minutos" value="{{ $servicio->duracion_minutos }}" min="10" max="240" step="5" required class="campo w-24 py-2">
                </div>
                <div>
                    <label class="etiqueta text-xs">Precio S/</label>
                    <input type="number" name="precio" value="{{ (float) $servicio->precio }}" min="0" step="0.5" required class="campo w-24 py-2">
                </div>
                <label class="flex items-center gap-2 pb-2.5 text-sm">
                    <input type="checkbox" name="activo" value="1" @checked($servicio->activo) class="size-4 accent-tinta"> Activo
                </label>
                <button class="btn-borde py-2">Guardar</button>
            </form>
        @endforeach
    </div>

    <h2 class="mt-10 font-display text-xl font-semibold">Nuevo servicio</h2>
    <form method="POST" action="{{ route('admin.servicios.guardar') }}" class="tarjeta mt-3 grid gap-3 p-4 sm:grid-cols-[2fr_3fr_auto_auto_auto] sm:items-end">
        @csrf
        <input type="hidden" name="activo" value="1">
        <div>
            <label class="etiqueta text-xs" for="nuevo-nombre">Nombre</label>
            <input id="nuevo-nombre" name="nombre" required maxlength="80" class="campo py-2" placeholder="Ej.: Tinte">
        </div>
        <div>
            <label class="etiqueta text-xs" for="nuevo-desc">Descripción</label>
            <input id="nuevo-desc" name="descripcion" maxlength="255" class="campo py-2">
        </div>
        <div>
            <label class="etiqueta text-xs" for="nuevo-min">Minutos</label>
            <input id="nuevo-min" type="number" name="duracion_minutos" value="30" min="10" max="240" step="5" required class="campo w-24 py-2">
        </div>
        <div>
            <label class="etiqueta text-xs" for="nuevo-precio">Precio S/</label>
            <input id="nuevo-precio" type="number" name="precio" min="0" step="0.5" required class="campo w-24 py-2">
        </div>
        <button class="btn-primario py-2">Agregar</button>
    </form>
</x-layouts.admin>
