<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla «solicitudes»: un turista pide una asesoría de viaje
     * o la renta de un vehículo, y el equipo de Explora RD (administrador) la atiende.
     */
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();

            // FK → usuarios: el turista que hace la solicitud.
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();

            // ¿Qué pide? asesoria = organizar el viaje; vehiculo = renta de vehículo
            $table->enum('servicio', ['asesoria', 'vehiculo']);

            // Asesoría: destino de interés. Vehículo: lugar de recogida.
            $table->string('lugar', 120);

            // Asesoría: llegada y salida del viaje. Vehículo: recogida y devolución.
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();

            $table->unsignedSmallInteger('cantidad_personas')->default(1);
            $table->string('tipo_vehiculo', 20)->nullable(); // solo para la renta
            $table->string('telefono', 30);
            $table->text('mensaje')->nullable();

            $table->enum('estado', ['pendiente', 'atendida', 'cancelada'])->default('pendiente');
            $table->text('respuesta')->nullable(); // nota del administrador para el turista
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
