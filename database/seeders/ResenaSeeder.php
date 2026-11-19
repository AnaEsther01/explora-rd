<?php

namespace Database\Seeders;

use App\Models\Excursion;
use App\Models\Resena;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Reseñas de prueba. Para poder opinar hay que haber hecho la excursión,
 * así que primero creamos reservas confirmadas con fechas que ya pasaron.
 *
 * También deja a María con una excursión ya hecha y SIN reseña
 * («Buggies y playa Macao»), para probar el botón «Escribir reseña».
 */
class ResenaSeeder extends Seeder
{
    public function run(): void
    {
        $maria = Usuario::where('correo', 'maria@correo.test')->firstOrFail();
        $john = Usuario::where('correo', 'john@correo.test')->firstOrFail();
        $excursion = fn (string $titulo) => Excursion::where('titulo', $titulo)->firstOrFail();

        $opiniones = [
            [$maria, 'Catamarán a isla Saona', 40, 5, '¡Increíble! La piscina natural es preciosa y el equipo fue muy amable. Volvería sin pensarlo.'],
            [$john, 'Catamarán a isla Saona', 25, 4, 'Great day, beautiful beach. The bus ride was a bit long, but worth it.'],
            [$maria, 'Experiencia de playa en Samaná', 60, 5, 'Playa Rincón es un paraíso. Todo muy bien organizado.'],
            [$john, 'Ruta gastronómica en la Zona Colonial', 15, 5, 'The food was amazing. Try the mangú!'],
            [$maria, 'Ruta gastronómica en la Zona Colonial', 50, 4, 'Muy rico todo, aunque el grupo era un poco grande.'],
            [$john, 'Rafting en el río Yaque del Norte', 30, 3, 'Fun, but we waited a long time to start.'],
        ];

        foreach ($opiniones as [$turista, $titulo, $diasAtras, $estrellas, $comentario]) {
            $ex = $excursion($titulo);
            $this->reservaPasada($turista, $ex, $diasAtras);

            $resena = Resena::firstOrCreate(
                ['usuario_id' => $turista->id, 'excursion_id' => $ex->id],
                ['estrellas' => $estrellas, 'comentario' => $comentario, 'visible' => true]
            );

            // La fecha de la reseña: el día después de la excursión (se ve más real)
            if ($resena->wasRecentlyCreated) {
                $dia = now()->subDays($diasAtras - 1);
                $resena->forceFill(['created_at' => $dia, 'updated_at' => $dia])->save();
            }
        }

        // Excursión ya hecha por María y todavía sin reseña
        $this->reservaPasada($maria, $excursion('Buggies y playa Macao'), 7);
    }

    /** Crea (si no existe) una reserva confirmada de hace unos días. */
    private function reservaPasada(Usuario $turista, Excursion $excursion, int $diasAtras): void
    {
        $existe = Reserva::where('usuario_id', $turista->id)
            ->where('excursion_id', $excursion->id)
            ->where('estado', 'confirmada')
            ->whereDate('fecha', '<', today())
            ->exists();

        if (! $existe) {
            Reserva::create([
                'usuario_id' => $turista->id,
                'excursion_id' => $excursion->id,
                'fecha' => now()->subDays($diasAtras)->toDateString(),
                'cantidad_personas' => 2,
                'estado' => 'confirmada',
            ]);
        }
    }
}
