// Paso a paso de la reserva: pide los turnos libres al servidor cada vez que
// cambia el servicio, el barbero o el día, y mantiene el resumen al día.

const form = document.getElementById('form-reserva');

if (form) {
    const zonaTurnos = document.getElementById('turnos');
    const resumen = document.getElementById('resumen');
    const resumenDetalle = document.getElementById('resumen-detalle');
    const boton = document.getElementById('btn-reservar');
    let horaPendiente = form.dataset.horaAnterior || null;
    let peticion = 0;

    const elegido = (nombre) => form.querySelector(`input[name="${nombre}"]:checked`);

    const mensaje = (texto) => {
        zonaTurnos.innerHTML = '';
        const p = document.createElement('p');
        p.className = 'rounded-xl bg-crema p-4 text-sm text-stone-600';
        p.textContent = texto;
        zonaTurnos.append(p);
    };

    // Deshabilita los días en que el barbero elegido no trabaja.
    const actualizarDias = () => {
        const barbero = elegido('barbero_id');
        const dias = barbero ? JSON.parse(barbero.dataset.dias) : null;

        form.querySelectorAll('input[name="fecha"]').forEach((input) => {
            input.disabled = dias !== null && !dias.includes(Number(input.dataset.dia));
            if (input.disabled && input.checked) input.checked = false;
        });

        if (!elegido('fecha')) {
            const primero = form.querySelector('input[name="fecha"]:not(:disabled)');
            if (primero) primero.checked = true;
        }
    };

    const actualizarResumen = () => {
        const servicio = elegido('servicio_id');
        const barbero = elegido('barbero_id');
        const fecha = elegido('fecha');
        const hora = elegido('hora');

        boton.disabled = !(servicio && barbero && fecha && hora);

        if (!servicio) {
            resumen.textContent = 'Aún no eliges un horario';
            resumenDetalle.textContent = '';
            return;
        }

        resumen.textContent = `${servicio.dataset.nombre} · S/ ${servicio.dataset.precio}`;
        resumenDetalle.textContent = [
            barbero?.dataset.nombre,
            fecha?.dataset.etiqueta,
            hora?.dataset.etiqueta,
        ].filter(Boolean).join(' · ');
    };

    const cargarTurnos = async () => {
        const servicio = elegido('servicio_id');
        const barbero = elegido('barbero_id');
        const fecha = elegido('fecha');

        actualizarResumen();

        if (!servicio || !barbero || !fecha) {
            mensaje('Elige servicio, barbero y día para ver los horarios libres.');
            return;
        }

        const id = ++peticion;
        mensaje('Buscando horarios libres…');

        const url = new URL(form.dataset.urlDisponibilidad);
        url.search = new URLSearchParams({
            servicio_id: servicio.value,
            barbero_id: barbero.value,
            fecha: fecha.value,
        });

        try {
            const respuesta = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!respuesta.ok) throw new Error(respuesta.status);
            const { turnos } = await respuesta.json();
            if (id !== peticion) return; // llegó una respuesta más nueva

            if (turnos.length === 0) {
                mensaje(`${barbero.dataset.nombre} no tiene horarios libres ese día. Prueba otro día u otro barbero.`);
                actualizarResumen();
                return;
            }

            const grilla = document.createElement('div');
            grilla.className = 'grid grid-cols-3 gap-2 sm:grid-cols-6';

            for (const turno of turnos) {
                const label = document.createElement('label');
                label.innerHTML = `
                    <input type="radio" name="hora" class="peer sr-only">
                    <span class="block cursor-pointer rounded-xl border border-stone-200 bg-white py-2.5 text-center text-sm font-medium transition hover:border-stone-400 peer-checked:border-tinta peer-checked:bg-tinta peer-checked:text-crema"></span>`;
                const input = label.querySelector('input');
                input.value = turno.hora;
                input.dataset.etiqueta = turno.etiqueta;
                input.checked = turno.hora === horaPendiente;
                label.querySelector('span').textContent = turno.etiqueta;
                grilla.append(label);
            }

            zonaTurnos.innerHTML = '';
            zonaTurnos.append(grilla);
            horaPendiente = null;
            actualizarResumen();
        } catch {
            if (id === peticion) mensaje('No pudimos cargar los horarios. Revisa tu conexión e inténtalo otra vez.');
        }
    };

    form.addEventListener('change', (evento) => {
        const nombre = evento.target.name;
        if (nombre === 'barbero_id') actualizarDias();
        if (['servicio_id', 'barbero_id', 'fecha'].includes(nombre)) cargarTurnos();
        if (nombre === 'hora') actualizarResumen();
    });

    form.addEventListener('submit', () => {
        boton.disabled = true;
        boton.textContent = 'Reservando…';
    });

    actualizarDias();
    cargarTurnos();
}
