<?php

return [
    'nombre' => env('BARBERIA_NOMBRE', 'Barbería Navaja'),
    'ciudad' => env('BARBERIA_CIUDAD', 'Huancayo'),
    'direccion' => env('BARBERIA_DIRECCION', 'Jr. Real 845, Huancayo'),
    // Número de WhatsApp del negocio, con código de país y sin "+".
    'whatsapp' => env('BARBERIA_WHATSAPP', '51900000000'),
    // Cada cuántos minutos empieza un turno.
    'intervalo_minutos' => (int) env('BARBERIA_INTERVALO', 30),
    // Anticipación mínima para reservar.
    'anticipacion_minutos' => (int) env('BARBERIA_ANTICIPACION', 60),
    // Cuántos días hacia adelante se puede reservar.
    'dias_reserva' => (int) env('BARBERIA_DIAS_RESERVA', 14),
];
