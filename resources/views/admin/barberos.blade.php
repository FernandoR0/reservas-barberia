<x-layouts.admin titulo="Barberos">
    <h1 class="font-display text-2xl font-semibold">Barberos y horarios</h1>
    <p class="text-sm text-stone-600">Cada día admite un turno de mañana y uno de tarde. Deja ambos vacíos para marcar el día como libre.</p>

    <div class="mt-6 space-y-4">
        @foreach ($barberos as $barbero)
            @php $porDia = $barbero->horarios->groupBy('dia_semana'); @endphp
            <details class="tarjeta group" @if ($loop->first) open @endif>
                <summary class="flex cursor-pointer list-none items-center gap-4 p-4">
                    <span class="grid size-11 place-items-center rounded-full bg-tinta font-display font-semibold text-laton-claro">{{ $barbero->iniciales() }}</span>
                    <span class="flex-1">
                        <span class="block font-semibold">{{ $barbero->nombre }}</span>
                        <span class="block text-sm text-stone-600">{{ $barbero->especialidad }} · {{ $porDia->count() }} días a la semana</span>
                    </span>
                    @unless ($barbero->activo)
                        <span class="rounded-full bg-stone-200 px-2 py-0.5 text-xs">Inactivo</span>
                    @endunless
                    <span class="text-stone-400 transition group-open:rotate-180" aria-hidden="true">▾</span>
                </summary>

                <form method="POST" action="{{ route('admin.barberos.actualizar', $barbero) }}" class="border-t border-stone-200 p-4">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                        <div>
                            <label class="etiqueta text-xs">Nombre</label>
                            <input name="nombre" value="{{ $barbero->nombre }}" required maxlength="80" class="campo py-2">
                        </div>
                        <div>
                            <label class="etiqueta text-xs">Especialidad</label>
                            <input name="especialidad" value="{{ $barbero->especialidad }}" maxlength="80" class="campo py-2">
                        </div>
                        <label class="flex items-center gap-2 pb-2.5 text-sm">
                            <input type="checkbox" name="activo" value="1" @checked($barbero->activo) class="size-4 accent-tinta"> Recibe reservas
                        </label>
                    </div>

                    <div class="mt-5 overflow-x-auto">
                        <table class="w-full min-w-[34rem] text-sm">
                            <thead class="text-left text-xs text-stone-500">
                                <tr><th class="py-1 font-medium">Día</th><th class="font-medium">Mañana</th><th class="font-medium">Tarde</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($dias as $numero => $dia)
                                    @php $bloques = $porDia->get($numero, collect())->values(); @endphp
                                    <tr class="border-t border-stone-100">
                                        <td class="py-2 pr-3 font-medium">{{ $dia }}</td>
                                        @foreach ([0, 1] as $turno)
                                            <td class="py-2 pr-3">
                                                <div class="flex items-center gap-1.5">
                                                    <input type="time" name="horario[{{ $numero }}][{{ $turno }}][inicio]" value="{{ substr($bloques[$turno]->hora_inicio ?? '', 0, 5) }}" class="campo w-auto px-2 py-1.5 text-sm" aria-label="{{ $dia }}, turno {{ $turno ? 'tarde' : 'mañana' }}, inicio">
                                                    <span class="text-stone-400">–</span>
                                                    <input type="time" name="horario[{{ $numero }}][{{ $turno }}][fin]" value="{{ substr($bloques[$turno]->hora_fin ?? '', 0, 5) }}" class="campo w-auto px-2 py-1.5 text-sm" aria-label="{{ $dia }}, turno {{ $turno ? 'tarde' : 'mañana' }}, fin">
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button class="btn-primario mt-4">Guardar cambios</button>
                </form>
            </details>
        @endforeach
    </div>

    <h2 class="mt-10 font-display text-xl font-semibold">Nuevo barbero</h2>
    <form method="POST" action="{{ route('admin.barberos.guardar') }}" class="tarjeta mt-3 grid gap-3 p-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
        @csrf
        <div>
            <label class="etiqueta text-xs" for="nb-nombre">Nombre</label>
            <input id="nb-nombre" name="nombre" required maxlength="80" class="campo py-2">
        </div>
        <div>
            <label class="etiqueta text-xs" for="nb-esp">Especialidad</label>
            <input id="nb-esp" name="especialidad" maxlength="80" class="campo py-2">
        </div>
        <button class="btn-primario py-2">Agregar</button>
    </form>
</x-layouts.admin>
