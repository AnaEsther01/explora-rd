<?php

namespace Database\Seeders;

use App\Models\Excursion;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Algunas reservas de prueba con los tres estados posibles,
 * para ver cómo se ven luego en el panel de la agencia.
 */
class ReservaSeeder extends Seeder
{
    public function run(): void
    {
        $maria = Usuario::where('correo', 'maria@correo.test')->firstOrFail();
        $john = Usuario::where('correo', 'john@correo.test')->firstOrFail();

        $excursion = fn (string $titulo) => Excursion::where('titulo', $titulo)->firstOrFail()->id;

        $reservas = [
            [
                'usuario_id' => $maria->id,
                'excursion_id' => $excursion('Rafting en el río Yaque del Norte'),
                'fecha' => now()->addDays(10)->toDateString(),
                'cantidad_personas' => 2,
                'estado' => 'pendiente',
                'comentario' => 'Somos principiantes, ¿es apto para nosotros?',
            ],
            [
                'usuario_id' => $maria->id,
                'excursion_id' => $excursion('Recorrido cultural por Santo Domingo'),
                'fecha' => now()->addDays(20)->toDateString(),
                'cantidad_personas' => 4,
                'estado' => 'confirmada',
                'comentario' => null,
            ],
            [
                'usuario_id' => $john->id,
                'excursion_id' => $excursion('Catamarán a isla Saona'),
                'fecha' => now()->addDays(5)->toDateString(),
                'cantidad_personas' => 2,
                'estado' => 'pendiente',
                'comentario' => 'We need an English-speaking guide, please.',
            ],
            [
                'usuario_id' => $john->id,
                'excursion_id' => $excursion('Buggies y playa Macao'),
                'fecha' => now()->addDays(3)->toDateString(),
                'cantidad_personas' => 1,
                'estado' => 'cancelada',
                'comentario' => null,
            ],
        ];

        // firstOrCreate: si ya existe la reserva de ese turista para esa excursión, no la repite
        foreach ($reservas as $reserva) {
            Reserva::firstOrCreate(
                ['usuario_id' => $reserva['usuario_id'], 'excursion_id' => $reserva['excursion_id']],
                $reserva
            );
        }
    }
}
