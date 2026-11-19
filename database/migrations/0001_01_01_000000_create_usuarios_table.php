<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla «usuarios» (turistas, agencias y administradores)
     * y dos tablas que Laravel necesita para las contraseñas y las sesiones.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('correo', 100)->unique();
            $table->string('contrasena', 255); // se guarda cifrada
            $table->string('telefono', 20)->nullable();
            $table->enum('rol', ['turista', 'agencia', 'administrador'])->default('turista');

            // Datos que solo llenan las agencias
            $table->string('nombre_comercial', 150)->nullable();
            $table->string('logo')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('direccion', 200)->nullable();

            $table->rememberToken(); // para la casilla «Recordarme»
            $table->timestamps();    // created_at y updated_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Deshace la migración (borra las tablas).
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
