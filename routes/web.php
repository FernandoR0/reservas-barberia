<?php

use App\Http\Controllers\Admin\AgendaController;
use App\Http\Controllers\Admin\BarberoController;
use App\Http\Controllers\Admin\ServicioController;
use App\Http\Controllers\Admin\SesionController;
use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Route;

// Sitio público
Route::get('/', [ReservaController::class, 'inicio'])->name('inicio');
Route::get('/reservar', [ReservaController::class, 'crear'])->name('reservas.crear');
Route::post('/reservar', [ReservaController::class, 'guardar'])->middleware('throttle:10,1')->name('reservas.guardar');
Route::get('/disponibilidad', [ReservaController::class, 'disponibilidad'])->middleware('throttle:60,1')->name('reservas.disponibilidad');
Route::get('/reserva/{reserva}', [ReservaController::class, 'ver'])->name('reservas.ver');

// Panel de administración
Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [SesionController::class, 'formulario'])->name('login');
        Route::post('/login', [SesionController::class, 'entrar'])->middleware('throttle:5,1');
    });

    Route::middleware('auth')->name('admin.')->group(function () {
        Route::post('/salir', [SesionController::class, 'salir'])->name('salir');
        Route::get('/', [AgendaController::class, 'index'])->name('agenda');
        Route::patch('/reservas/{reserva}/estado', [AgendaController::class, 'estado'])->name('reservas.estado');

        Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios');
        Route::post('/servicios', [ServicioController::class, 'guardar'])->name('servicios.guardar');
        Route::put('/servicios/{servicio}', [ServicioController::class, 'actualizar'])->name('servicios.actualizar');

        Route::get('/barberos', [BarberoController::class, 'index'])->name('barberos');
        Route::post('/barberos', [BarberoController::class, 'guardar'])->name('barberos.guardar');
        Route::put('/barberos/{barbero}', [BarberoController::class, 'actualizar'])->name('barberos.actualizar');
    });
});
