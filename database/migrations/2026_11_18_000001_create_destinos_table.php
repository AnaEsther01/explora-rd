<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla «destinos» (Jarabacoa, Punta Cana, etc.).
     */
    public function up(): void
    {
        Schema::create('destinos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('provincia', 100);
            $table->text('descripcion');
            $table->enum('tipo', ['playa', 'montaña', 'ciudad']);
            $table->string('foto')->nullable(); // ruta dentro de public/img
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinos');
    }
};
