<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla «reservas»: un turista solicita una excursión
     * y la agencia la confirma o la cancela.
     */
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();

            // FK → usuarios: el turista que reserva.
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();

            // FK → excursiones: la excursión reservada.
            $table->foreignId('excursion_id')->constrained('excursiones')->cascadeOnDelete();

            $table->date('fecha');
            $table->unsignedSmallInteger('cantidad_personas')->default(1);
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada'])->default('pendiente');
            $table->text('comentario')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
