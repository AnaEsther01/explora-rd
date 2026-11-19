<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * «traducciones»: la descripción y las actividades de cada destino en otros idiomas.
     * Ejemplo: {"en": {"descripcion": "...", "actividades": ["..."]}, "fr": {...}}
     * El español sigue en las columnas descripcion y actividades.
     */
    public function up(): void
    {
        Schema::table('destinos', function (Blueprint $table) {
            $table->json('traducciones')->nullable()->after('actividades');
        });
    }

    public function down(): void
    {
        Schema::table('destinos', function (Blueprint $table) {
            $table->dropColumn('traducciones');
        });
    }
};
