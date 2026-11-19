<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // Nota: no usamos «WithoutModelEvents» porque los modelos Destino y Excursion
    // crean su slug (dirección web) en el evento «saving» al guardarse.

    /**
     * Llena la base de datos con datos de prueba.
     * El orden importa: las excursiones necesitan usuarios y destinos,
     * y las reservas necesitan excursiones.
     */
    public function run(): void
    {
        $this->call([
            UsuarioSeeder::class,
            DestinoSeeder::class,
            TraduccionDestinoSeeder::class,
            UbicacionDestinoSeeder::class,
            ExcursionSeeder::class,
            ReservaSeeder::class,
            SolicitudSeeder::class,
            ResenaSeeder::class,
            FavoritoSeeder::class,
        ]);
    }
}
