<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Etapa 6: datos extra del perfil de cada agencia.
     * - fotos:   lista de fotos de la agencia (rutas dentro de public/)
     * - videos:  lista de enlaces de videos (YouTube o Vimeo)
     * - visitas: cuántas veces se abrió su perfil público
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->json('fotos')->nullable()->after('direccion');
            $table->json('videos')->nullable()->after('fotos');
            $table->unsignedInteger('visitas')->default(0)->after('videos');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['fotos', 'videos', 'visitas']);
        });
    }
};
