<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Etapa 4: campos nuevos para las páginas de destinos y excursiones.
     * - slug: el nombre en formato de dirección web (ej.: «bahia-de-las-aguilas»)
     *   para que las direcciones sean /destinos/jarabacoa en vez de /destinos/1.
     * - actividades: lista de cosas que hacer en cada destino.
     * - categoria: playas, montaña, catamarán, cultura, gastronomía, familia o aventura.
     */
    public function up(): void
    {
        Schema::table('destinos', function (Blueprint $table) {
            $table->string('slug', 120)->nullable()->unique()->after('nombre');
            $table->json('actividades')->nullable()->after('descripcion');
        });

        Schema::table('excursiones', function (Blueprint $table) {
            $table->string('slug', 170)->nullable()->unique()->after('titulo');
            $table->string('categoria', 30)->nullable()->after('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('destinos', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'actividades']);
        });

        Schema::table('excursiones', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'categoria']);
        });
    }
};
