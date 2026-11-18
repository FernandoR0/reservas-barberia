# Barbería Navaja · Sistema de reservas

> **Proyecto demostrativo.** El negocio y los datos son ficticios.

Sistema de citas en línea para una barbería. El cliente elige servicio, barbero, día y hora libre desde el celular, recibe un código y confirma por WhatsApp. El dueño gestiona la agenda, los servicios y los horarios de cada barbero desde un panel.

## El problema que resuelve

Muchas barberías de Huancayo toman citas por WhatsApp o en un cuaderno. Eso causa cruces de horario, clientes que esperan sin cita y tiempo perdido respondiendo "¿a qué hora hay espacio?". Este sistema muestra solo los horarios realmente libres y deja todo registrado.

## Funciones

**Para el cliente**
- Reserva en 4 pasos, pensada para celular.
- Solo ve los horarios libres según el horario del barbero, la duración del servicio y las citas ya tomadas.
- Bloquea los días en que el barbero no trabaja.
- Recibe un código de reserva y un botón de WhatsApp con el mensaje ya escrito.

**Para el negocio** (`/admin`)
- Agenda por día con filtros por barbero y estado.
- Resumen de citas del día, pendientes e ingresos del día y de la semana.
- Confirmar, marcar como atendida o cancelar con un clic. Una cita cancelada libera el horario.
- Botón de WhatsApp al cliente con el mensaje adecuado según el estado.
- Gestión de servicios (precio, duración, activo) y barberos con horario semanal de mañana y tarde.

**Detalles técnicos**
- Evita reservas duplicadas aunque dos personas reserven a la vez: transacción con bloqueo de fila (`lockForUpdate`) y nueva comprobación del horario.
- Validación de celular peruano y límite de peticiones en la reserva y el login.
- El código de la reserva no basta para ver los datos de otra persona: solo los ve el navegador que la hizo.
- 13 pruebas automáticas (PHPUnit) que cubren el cálculo de horarios, los cruces, la anticipación mínima, la validación y el panel.

## Tecnologías

Laravel 13 · PHP 8.4 · MySQL 8.4 · Blade · Tailwind CSS 4 · Vite · JavaScript sin framework · Docker Compose · PHPUnit

## Cómo ejecutarlo

Requisitos: Docker Desktop y Node.js 20 o más reciente.

```bash
cp .env.example .env
npm install
npm run build
docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate
docker compose up -d --build
docker compose exec app php artisan db:seed
```

Abre http://localhost:8000. El panel está en http://localhost:8000/admin.

**Acceso de demo al panel:** `admin@navaja.test` / `navaja-demo-2026`. Se definen en `.env` con `ADMIN_EMAIL` y `ADMIN_PASSWORD`.

Pruebas:

```bash
docker compose exec app php artisan test
```

## Configuración del negocio

En `.env`:

| Variable | Para qué sirve |
|---|---|
| `BARBERIA_NOMBRE` | Nombre que aparece en el sitio y en los mensajes |
| `BARBERIA_WHATSAPP` | Número del negocio con código de país, sin "+" (ej. `51987654321`) |
| `BARBERIA_INTERVALO` | Cada cuántos minutos empieza un turno (30 por defecto) |
| `BARBERIA_ANTICIPACION` | Minutos mínimos de anticipación para reservar (60) |
| `BARBERIA_DIAS_RESERVA` | Cuántos días hacia adelante se puede reservar (14) |

## Dónde está cada cosa

- `app/Services/Disponibilidad.php`: cálculo de horarios libres (el corazón del sistema).
- `app/Http/Controllers/ReservaController.php`: flujo público de reserva.
- `app/Http/Controllers/Admin/`: agenda, servicios, barberos y sesión.
- `app/Support/WhatsApp.php`: enlaces `wa.me` con mensajes ya escritos.
- `resources/js/reservar.js`: paso a paso de la reserva en el navegador.
- `tests/Feature/ReservaTest.php`: pruebas.

## Posibles mejoras

- Recordatorio automático por WhatsApp un día antes (API de WhatsApp Business).
- Pago de adelanto con Yape o tarjeta (Culqi / Mercado Pago).
- Reservas creadas desde el panel para clientes que llaman por teléfono.
- Opción "cualquier barbero disponible".
