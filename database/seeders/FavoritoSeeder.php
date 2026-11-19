<?php

namespace Database\Seeders;

use App\Models\Excursion;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Favoritos de prueba: María guardó dos excursiones con el corazón ♥.
 */
class FavoritoSeeder extends Seeder
{
    public function run(): void
    {
        $maria = Usuario::where('correo', 'maria@correo.test')->firstOrFail();

        $ids = Excursion::whereIn('titulo', ['Bahía de las Águilas en bote', 'Ruta de aventura por Jarabacoa'])->pluck('id');

        // syncWithoutDetaching: agrega las que falten sin borrar las que ya tenía
        $maria->favoritas()->syncWithoutDetaching($ids);
    }
}
