<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla «resenas»: la opinión (1 a 5 estrellas y un comentario)
     * que deja un turista después de hacer una excursión.
     * (Sin «ñ» en el nombre de la tabla para evitar problemas con MySQL.)
     */
    public function up(): void
    {
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();

            // FK → usuarios: el turista que opina.
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();

            // FK → excursiones: la excursión de la que opina.
            $table->foreignId('excursion_id')->constrained('excursiones')->cascadeOnDelete();

            $table->unsignedTinyInteger('estrellas'); // de 1 a 5
            $table->text('comentario')->nullable();

            // El administrador puede ocultar una reseña ofensiva sin borrarla
            $table->boolean('visible')->default(true);
            $table->timestamps();

            // Cada turista deja UNA sola reseña por excursión (puede editarla)
            $table->unique(['usuario_id', 'excursion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resenas');
    }
};
