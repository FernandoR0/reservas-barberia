<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 8)->unique();
            $table->foreignId('servicio_id')->constrained('servicios')->restrictOnDelete();
            $table->foreignId('barbero_id')->constrained('barberos')->restrictOnDelete();
            $table->string('cliente_nombre');
            $table->string('cliente_telefono', 15);
            $table->dateTime('inicia_en');
            $table->dateTime('termina_en');
            $table->decimal('precio', 8, 2);
            $table->string('estado', 12)->default('pendiente');
            $table->string('notas', 500)->nullable();
            $table->timestamps();

            $table->index(['barbero_id', 'inicia_en']);
            $table->index(['estado', 'inicia_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
