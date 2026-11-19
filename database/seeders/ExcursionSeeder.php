<?php

namespace Database\Seeders;

use App\Models\Destino;
use App\Models\Excursion;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Excursiones, circuitos y paquetes de prueba (precios en pesos dominicanos).
 */
class ExcursionSeeder extends Seeder
{
    public function run(): void
    {
        // Buscamos las agencias y los destinos que crearon los seeders anteriores.
        $aventuras = Usuario::where('correo', 'contacto@aventurasjarabacoa.test')->firstOrFail();
        $brisa = Usuario::where('correo', 'info@brisatropical.test')->firstOrFail();

        $destino = fn (string $nombre) => Destino::where('nombre', $nombre)->firstOrFail()->id;

        $excursiones = [
            // --- Aventuras Jarabacoa ---
            [
                'agencia_id' => $aventuras->id,
                'destino_id' => $destino('Jarabacoa'),
                'tipo' => 'excursion',
                'titulo' => 'Rafting en el río Yaque del Norte',
                'descripcion' => 'Baja los rápidos del río más largo del país con guías certificados. Incluye equipo de seguridad y almuerzo típico.',
                'precio' => 3500,
                'duracion' => '5 horas',
                'ubicacion' => 'Jarabacoa, La Vega',
                'foto' => 'img/excursiones/rafting-jarabacoa.jpg',
            ],
            [
                'agencia_id' => $aventuras->id,
                'destino_id' => $destino('Jarabacoa'),
                'tipo' => 'excursion',
                'titulo' => 'Saltos de Jimenoa y Baiguate',
                'descripcion' => 'Caminata por senderos de montaña para conocer las dos cascadas más famosas de Jarabacoa. Actividad ideal para familias.',
                'precio' => 2200,
                'duracion' => '4 horas',
                'ubicacion' => 'Jarabacoa, La Vega',
                'foto' => 'img/excursiones/saltos-jarabacoa.jpg',
            ],
            [
                'agencia_id' => $aventuras->id,
                'destino_id' => $destino('Jarabacoa'),
                'tipo' => 'circuito',
                'titulo' => 'Ruta de aventura por Jarabacoa',
                'descripcion' => 'Dos días de aventura: rafting, parapente, cascadas y noche en cabaña de montaña. Incluye transporte, comidas y alojamiento.',
                'precio' => 9800,
                'duracion' => '2 días',
                'ubicacion' => 'Jarabacoa, La Vega',
                'foto' => 'img/excursiones/ruta-aventura-jarabacoa.jpg',
            ],
            [
                'agencia_id' => $aventuras->id,
                'destino_id' => $destino('Bahía de las Águilas'),
                'tipo' => 'excursion',
                'titulo' => 'Bahía de las Águilas en bote',
                'descripcion' => 'Paseo en bote desde Cabo Rojo hasta Bahía de las Águilas, con tiempo libre para nadar y hacer snorkel.',
                'precio' => 4500,
                'duracion' => 'Día completo',
                'ubicacion' => 'Cabo Rojo, Pedernales',
                'foto' => 'img/excursiones/bahia-aguilas-bote.jpg',
            ],

            // --- Brisa Tropical Tours ---
            [
                'agencia_id' => $brisa->id,
                'destino_id' => $destino('Punta Cana'),
                'tipo' => 'excursion',
                'titulo' => 'Catamarán a isla Saona',
                'descripcion' => 'Navega en catamarán hasta isla Saona, para en la piscina natural y disfruta de un almuerzo buffet en la playa.',
                'precio' => 5900,
                'duracion' => 'Día completo',
                'ubicacion' => 'Bayahíbe, La Altagracia',
                'foto' => 'img/excursiones/catamaran-saona.jpg',
            ],
            [
                'agencia_id' => $brisa->id,
                'destino_id' => $destino('Punta Cana'),
                'tipo' => 'excursion',
                'titulo' => 'Buggies y playa Macao',
                'descripcion' => 'Recorre caminos rurales en buggy, visita una cueva con agua cristalina y termina en playa Macao.',
                'precio' => 3800,
                'duracion' => '4 horas',
                'ubicacion' => 'Punta Cana, La Altagracia',
                'foto' => 'img/excursiones/buggies-macao.jpg',
            ],
            [
                'agencia_id' => $brisa->id,
                'destino_id' => $destino('Santo Domingo'),
                'tipo' => 'circuito',
                'titulo' => 'Recorrido cultural por Santo Domingo',
                'descripcion' => 'Visita guiada por la Zona Colonial: Catedral Primada, Alcázar de Colón, calle El Conde y fortaleza Ozama.',
                'precio' => 2900,
                'duracion' => '6 horas',
                'ubicacion' => 'Zona Colonial, Santo Domingo',
                'foto' => 'img/excursiones/recorrido-cultural-sd.jpg',
            ],
            [
                'agencia_id' => $brisa->id,
                'destino_id' => $destino('Santo Domingo'),
                'tipo' => 'excursion',
                'titulo' => 'Ruta gastronómica en la Zona Colonial',
                'descripcion' => 'Prueba la comida dominicana: mangú, sancocho, chicharrón, dulces criollos y café, en cinco paradas.',
                'precio' => 2500,
                'duracion' => '3 horas',
                'ubicacion' => 'Zona Colonial, Santo Domingo',
                'foto' => 'img/excursiones/ruta-gastronomica.jpg',
            ],
            [
                'agencia_id' => $brisa->id,
                'destino_id' => $destino('Samaná'),
                'tipo' => 'paquete',
                'titulo' => 'Experiencia de playa en Samaná',
                'descripcion' => 'Paquete de 3 días y 2 noches: playa Rincón, salto El Limón y cayo Levantado. Incluye hotel, desayunos y transporte.',
                'precio' => 18500,
                'duracion' => '3 días / 2 noches',
                'ubicacion' => 'Las Terrenas, Samaná',
                'foto' => 'img/excursiones/playa-samana.jpg',
            ],
        ];

        // Categoría de cada experiencia (playas, montaña, catamarán, cultura, gastronomía, familia, aventura)
        $categorias = [
            'Rafting en el río Yaque del Norte' => 'aventura',
            'Saltos de Jimenoa y Baiguate' => 'familia',
            'Ruta de aventura por Jarabacoa' => 'montana',
            'Bahía de las Águilas en bote' => 'playas',
            'Catamarán a isla Saona' => 'catamaran',
            'Buggies y playa Macao' => 'aventura',
            'Recorrido cultural por Santo Domingo' => 'cultura',
            'Ruta gastronómica en la Zona Colonial' => 'gastronomia',
            'Experiencia de playa en Samaná' => 'playas',
        ];

        // updateOrCreate: actualiza la excursión si ya existe (mismo título) o la crea.
        // Así se puede volver a ejecutar sin duplicar y sin perder las reservas.
        foreach ($excursiones as $excursion) {
            $excursion['categoria'] = $categorias[$excursion['titulo']] ?? null;
            Excursion::updateOrCreate(['titulo' => $excursion['titulo']], $excursion);
        }
    }
}
