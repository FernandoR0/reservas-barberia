<?php

namespace App\Support;

use App\Enums\EstadoReserva;
use App\Models\Reserva;

/**
 * Enlaces wa.me con el mensaje ya escrito. No necesita la API de WhatsApp
 * Business: se abre el chat y la persona solo pulsa "Enviar".
 */
class WhatsApp
{
    public static function enlace(string $numero, string $mensaje): string
    {
        return 'https://wa.me/'.preg_replace('/\D/', '', $numero).'?text='.rawurlencode($mensaje);
    }

    /** Lo que el cliente envía al negocio después de reservar. */
    public static function clienteAlNegocio(Reserva $r): string
    {
        $mensaje = "Hola, acabo de reservar en ".config('barberia.nombre').":\n"
            ."• Código: {$r->codigo}\n"
            ."• {$r->servicio->nombre} con {$r->barbero->nombre}\n"
            .'• '.self::fecha($r)."\n"
            ."A nombre de {$r->cliente_nombre}. ¿Me confirman, por favor?";

        return self::enlace(config('barberia.whatsapp'), $mensaje);
    }

    /** Lo que el negocio envía al cliente desde el panel. */
    public static function negocioAlCliente(Reserva $r): string
    {
        $nombre = strtok($r->cliente_nombre, ' ');
        $detalle = "{$r->servicio->nombre} con {$r->barbero->nombre}, ".self::fecha($r);

        $mensaje = match ($r->estado) {
            EstadoReserva::Pendiente, EstadoReserva::Confirmada => "Hola {$nombre}, te confirmamos tu cita en "
                .config('barberia.nombre').": {$detalle}. Código {$r->codigo}. "
                .'Estamos en '.config('barberia.direccion').'. Si no puedes venir, avísanos por aquí. ¡Te esperamos!',
            EstadoReserva::Cancelada => "Hola {$nombre}, tu cita del ".self::fecha($r)
                .' fue cancelada. Si quieres, te ayudamos a elegir otro horario.',
            EstadoReserva::Completada => "Gracias por tu visita, {$nombre}. ¡Te esperamos pronto en "
                .config('barberia.nombre').'!',
        };

        return self::enlace($r->telefonoWhatsapp(), $mensaje);
    }

    private static function fecha(Reserva $r): string
    {
        return $r->inicia_en->locale('es')->translatedFormat('l j \d\e F, g:i a');
    }
}
