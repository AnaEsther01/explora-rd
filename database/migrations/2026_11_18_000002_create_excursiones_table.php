<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla «excursiones». Guarda excursiones, circuitos y paquetes.
     * Debe crearse después de «usuarios» y «destinos» porque depende de ellas.
     */
    public function up(): void
    {
        Schema::create('excursiones', function (Blueprint $table) {
            $table->id();

            // FK → usuarios: la agencia que publica la excursión.
            // Si se borra la agencia, se borran sus excursiones.
            $table->foreignId('agencia_id')->constrained('usuarios')->cascadeOnDelete();

            // FK → destinos: no se puede borrar un destino que tenga excursiones.
            $table->foreignId('destino_id')->constrained('destinos')->restrictOnDelete();

            $table->enum('tipo', ['excursion', 'circuito', 'paquete'])->default('excursion');
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->string('duracion', 50)->nullable();
            $table->string('ubicacion', 150)->nullable();
            $table->string('foto')->nullable(); // ruta dentro de public/img
            $table->boolean('disponible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excursiones');
    }
};
