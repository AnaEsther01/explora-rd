<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la ubicación en el mapa (latitud y longitud) a cada destino.
     * Ejemplo: Jarabacoa está en latitud 19.1167 y longitud -70.6333.
     * Son opcionales: un destino sin coordenadas simplemente no sale en el mapa.
     */
    public function up(): void
    {
        Schema::table('destinos', function (Blueprint $table) {
            $table->decimal('latitud', 9, 6)->nullable()->after('tipo');
            $table->decimal('longitud', 9, 6)->nullable()->after('latitud');
        });
    }

    public function down(): void
    {
        Schema::table('destinos', function (Blueprint $table) {
            $table->dropColumn(['latitud', 'longitud']);
        });
    }
};
