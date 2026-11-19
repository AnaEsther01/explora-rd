<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Datos para la página PUBLICADA.
 * Solo carga los destinos (con sus fotos, actividades, traducciones y ubicación en el mapa).
 * NO crea cuentas de prueba ni excursiones inventadas: las agencias reales
 * se registran solas y publican sus propias excursiones.
 *
 * Uso en el servidor:  php artisan db:seed --class=ProduccionSeeder --force
 * Luego, para el administrador:  php artisan explora:crear-admin
 */
class ProduccionSeeder extends Seeder
{
    public function run(): void
    {
        // Destinos, sus traducciones y su ubicación en el mapa
        $this->call([DestinoSeeder::class, TraduccionDestinoSeeder::class, UbicacionDestinoSeeder::class]);
    }
}
