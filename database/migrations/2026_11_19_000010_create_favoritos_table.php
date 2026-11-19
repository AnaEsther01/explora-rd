<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla «favoritos»: las excursiones que un turista guarda con el corazón ♥.
     * Es una tabla intermedia (relación muchos a muchos): un turista guarda muchas
     * excursiones y una excursión puede estar guardada por muchos turistas.
     */
    public function up(): void
    {
        Schema::create('favoritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('excursion_id')->constrained('excursiones')->cascadeOnDelete();
            $table->timestamps();

            // La misma excursión no se guarda dos veces
            $table->unique(['usuario_id', 'excursion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favoritos');
    }
};
