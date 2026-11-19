<?php

namespace Database\Seeders;

use App\Models\Destino;
use Illuminate\Database\Seeder;

/**
 * Descripción y actividades de cada destino en INGLÉS y FRANCÉS.
 * El español está en DestinoSeeder. Se busca el destino por su nombre.
 */
class TraduccionDestinoSeeder extends Seeder
{
    public function run(): void
    {
        $traducciones = [
            'Jarabacoa' => [
                'en' => [
                    'descripcion' => 'Mountains, rivers and nature. Known as "the city of eternal spring", Jarabacoa has a cool climate all year round, crystal-clear rivers and the most famous waterfalls in the country. It is the perfect place for adventure and ecotourism in the heart of the Cordillera Central.',
                    'actividades' => ['Rafting on the Yaque del Norte river', 'Jimenoa and Baiguate waterfalls', 'Paragliding over the valley', 'Hiking and horseback riding', 'Climbing Pico Duarte (starting point)'],
                ],
                'fr' => [
                    'descripcion' => 'Montagnes, rivières et nature. Surnommée « la ville de l\'éternel printemps », Jarabacoa offre un climat frais toute l\'année, des rivières cristallines et les cascades les plus célèbres du pays. C\'est l\'endroit idéal pour l\'aventure et l\'écotourisme au cœur de la Cordillère centrale.',
                    'actividades' => ['Rafting sur la rivière Yaque del Norte', 'Cascades de Jimenoa et de Baiguate', 'Parapente au-dessus de la vallée', 'Randonnée et balades à cheval', 'Ascension du pic Duarte (point de départ)'],
                ],
            ],
            'Bahía de las Águilas' => [
                'en' => [
                    'descripcion' => 'Beaches and natural landscapes. Considered one of the most unspoiled beaches in the Caribbean, it lies within Jaragua National Park. Eight kilometres of white sand and turquoise water, with no hotels or buildings, reached by boat or four-wheel-drive vehicle.',
                    'actividades' => ['Boat trip from Cabo Rojo', 'Snorkelling in crystal-clear water', 'Visit to Oviedo Lagoon', 'Iguana and bird watching', 'Camping in permitted areas'],
                ],
                'fr' => [
                    'descripcion' => 'Plages et paysages naturels. Considérée comme l\'une des plages les plus sauvages des Caraïbes, elle se trouve dans le parc national Jaragua. Huit kilomètres de sable blanc et d\'eau turquoise, sans hôtels ni constructions, accessibles en bateau ou en véhicule tout-terrain.',
                    'actividades' => ['Balade en bateau depuis Cabo Rojo', 'Snorkeling dans une eau cristalline', 'Visite de la lagune d\'Oviedo', 'Observation des iguanes et des oiseaux', 'Camping dans les zones autorisées'],
                ],
            ],
            'Punta Cana' => [
                'en' => [
                    'descripcion' => 'Beaches and water activities. The most visited destination in the country: miles of white-sand beaches lined with coconut palms, calm waters and a wide range of excursions, from catamarans to buggy adventures.',
                    'actividades' => ['Catamaran to Saona Island', 'Buggies and Macao beach', 'Snorkelling and diving on the reefs', 'Hoyo Azul and Scape Park', 'Sport fishing'],
                ],
                'fr' => [
                    'descripcion' => 'Plages et activités nautiques. La destination la plus visitée du pays : des kilomètres de plages de sable blanc bordées de cocotiers, des eaux calmes et un grand choix d\'excursions, du catamaran aux aventures en buggy.',
                    'actividades' => ['Catamaran vers l\'île Saona', 'Buggies et plage de Macao', 'Snorkeling et plongée sur les récifs', 'Hoyo Azul et Scape Park', 'Pêche sportive'],
                ],
            ],
            'Santo Domingo' => [
                'en' => [
                    'descripcion' => 'History, culture and food. The first city founded by Europeans in the Americas. Its Colonial Zone, a World Heritage Site, is home to the first cathedral of the continent and stone streets full of museums, squares, restaurants and nightlife.',
                    'actividades' => ['Tour of the Colonial Zone', 'Cathedral of Santa María la Menor', 'Alcázar de Colón and Museum of the Royal Houses', 'Dominican food tour', 'Los Tres Ojos caves and the Malecón'],
                ],
                'fr' => [
                    'descripcion' => 'Histoire, culture et gastronomie. La première ville fondée par les Européens en Amérique. Sa Zone coloniale, inscrite au patrimoine mondial, abrite la première cathédrale du continent et des rues pavées pleines de musées, de places, de restaurants et de vie nocturne.',
                    'actividades' => ['Visite de la Zone coloniale', 'Cathédrale primatiale d\'Amérique', 'Alcázar de Colón et musée des Maisons royales', 'Parcours gastronomique dominicain', 'Grottes de Los Tres Ojos et le Malecón'],
                ],
            ],
            'Samaná' => [
                'en' => [
                    'descripcion' => 'Beaches, waterfalls and whales. A peninsula of lush nature with some of the most beautiful beaches in the country, such as Rincón and Frontón. From January to March thousands of humpback whales arrive in its bay.',
                    'actividades' => ['Whale watching (January to March)', 'El Limón waterfall', 'Cayo Levantado', 'Rincón and Frontón beaches', 'Los Haitises National Park'],
                ],
                'fr' => [
                    'descripcion' => 'Plages, cascades et baleines. Une péninsule à la nature luxuriante avec certaines des plus belles plages du pays, comme Rincón et Frontón. De janvier à mars, des milliers de baleines à bosse arrivent dans sa baie.',
                    'actividades' => ['Observation des baleines (janvier à mars)', 'Cascade El Limón', 'Cayo Levantado', 'Plages de Rincón et de Frontón', 'Parc national Los Haitises'],
                ],
            ],
            'Puerto Plata' => [
                'en' => [
                    'descripcion' => 'Beaches, cable car and adventure. The "Bride of the Atlantic" combines beaches such as Sosúa and Cabarete with the only cable car in the Caribbean, which climbs Mount Isabel de Torres, and the famous 27 Waterfalls of Damajagua.',
                    'actividades' => ['Cable car to Mount Isabel de Torres', '27 Waterfalls of Damajagua', 'Kitesurfing in Cabarete', 'Sosúa beach', 'San Felipe Fortress'],
                ],
                'fr' => [
                    'descripcion' => 'Plages, téléphérique et aventure. La « Fiancée de l\'Atlantique » associe des plages comme Sosúa et Cabarete au seul téléphérique des Caraïbes, qui monte au mont Isabel de Torres, et aux célèbres 27 cascades de Damajagua.',
                    'actividades' => ['Téléphérique du mont Isabel de Torres', '27 cascades de Damajagua', 'Kitesurf à Cabarete', 'Plage de Sosúa', 'Forteresse San Felipe'],
                ],
            ],
            'La Romana' => [
                'en' => [
                    'descripcion' => 'Beaches, diving and islands. Boat trips to Saona and Catalina islands leave from the fishing village of Bayahíbe. Nearby is Altos de Chavón, a Mediterranean-style stone village overlooking the Chavón river.',
                    'actividades' => ['Catalina and Saona islands', 'Diving in East National Park', 'Altos de Chavón', 'Dominicus beach', 'Cueva de las Maravillas'],
                ],
                'fr' => [
                    'descripcion' => 'Plages, plongée et îles. Les excursions vers les îles Saona et Catalina partent du village de pêcheurs de Bayahíbe. Tout près se trouve Altos de Chavón, un village de pierre de style méditerranéen dominant la rivière Chavón.',
                    'actividades' => ['Îles Catalina et Saona', 'Plongée dans le parc national de l\'Est', 'Altos de Chavón', 'Plage de Dominicus', 'Grotte des Merveilles'],
                ],
            ],
            'Barahona' => [
                'en' => [
                    'descripcion' => 'Where the mountains meet the sea. The Barahona coast has pebble beaches with turquoise water, rivers that flow from the mountains to the sea, the curious Magnetic Pole and the larimar mines, the national gemstone.',
                    'actividades' => ['San Rafael river pools', 'Magnetic Pole', 'Larimar mines', 'Los Patos beach', 'Lake Enriquillo'],
                ],
                'fr' => [
                    'descripcion' => 'Là où la montagne rencontre la mer. La côte de Barahona offre des plages de galets aux eaux turquoise, des rivières qui descendent de la montagne jusqu\'à la mer, le curieux Pôle magnétique et les mines de larimar, la pierre nationale.',
                    'actividades' => ['Bassins de la rivière San Rafael', 'Pôle magnétique', 'Mines de larimar', 'Plage de Los Patos', 'Lac Enriquillo'],
                ],
            ],
            'Constanza' => [
                'en' => [
                    'descripcion' => 'Mountains, cool weather and flower fields. The highest valley in the Caribbean, more than 1,200 metres above sea level. A cool climate, strawberry and flower farms, mountain cabins and the impressive Aguas Blancas waterfall.',
                    'actividades' => ['Aguas Blancas waterfall', 'Valle Nuevo and the Pyramids', 'Flower and strawberry fields', 'Mountain cabins', 'Hiking'],
                ],
                'fr' => [
                    'descripcion' => 'Montagnes, fraîcheur et champs de fleurs. La plus haute vallée des Caraïbes, à plus de 1 200 mètres d\'altitude. Un climat frais, des cultures de fraises et de fleurs, des chalets de montagne et l\'impressionnante cascade Aguas Blancas.',
                    'actividades' => ['Cascade Aguas Blancas', 'Valle Nuevo et les Pyramides', 'Champs de fleurs et de fraises', 'Chalets de montagne', 'Randonnée'],
                ],
            ],
            'Montecristi' => [
                'en' => [
                    'descripcion' => 'Nature and unique landscapes. In the far northwest of the country, Montecristi surprises visitors with El Morro, a flat-topped mountain by the sea, the Seven Brothers cays, mangroves and salt flats.',
                    'actividades' => ['Climbing El Morro', 'Seven Brothers cays', 'Mangrove tour', 'Montecristi salt flats', 'Historic town clock'],
                ],
                'fr' => [
                    'descripcion' => 'Nature et paysages uniques. À l\'extrême nord-ouest du pays, Montecristi surprend avec El Morro, une montagne tabulaire face à la mer, les cayes des Sept Frères, des mangroves et des salines.',
                    'actividades' => ['Ascension d\'El Morro', 'Cayes des Sept Frères', 'Balade dans les mangroves', 'Salines de Montecristi', 'Horloge du centre historique'],
                ],
            ],
            'Santiago' => [
                'en' => [
                    'descripcion' => 'Culture, carnival and tobacco. The "Heart City" is the second largest city in the country. It is home to the Monument to the Heroes of the Restoration, the Centro León, cigar factories and one of the most colourful carnivals in the Caribbean.',
                    'actividades' => ['Monument to the Heroes of the Restoration', 'Centro León', 'Tobacco route', 'Santiago Carnival (February)', 'Cibao cuisine'],
                ],
                'fr' => [
                    'descripcion' => 'Culture, carnaval et tabac. La « Ville cœur » est la deuxième ville du pays. On y trouve le Monument aux héros de la Restauration, le Centro León, des fabriques de cigares et l\'un des carnavals les plus colorés des Caraïbes.',
                    'actividades' => ['Monument aux héros de la Restauration', 'Centro León', 'Route du tabac', 'Carnaval de Santiago (février)', 'Cuisine du Cibao'],
                ],
            ],
            'Miches' => [
                'en' => [
                    'descripcion' => 'Unspoiled beaches and tranquillity. One of the best-kept secrets of the east: Esmeralda beach and Costa Esmeralda, with golden sand, coconut palms and calm waters, far from the crowds.',
                    'actividades' => ['Esmeralda beach', 'Redonda and Limón lagoons', 'Montaña Redonda', 'Horseback riding', 'Traditional fishing'],
                ],
                'fr' => [
                    'descripcion' => 'Plages sauvages et tranquillité. L\'un des secrets les mieux gardés de l\'est : la plage Esmeralda et la Costa Esmeralda, avec leur sable doré, leurs cocotiers et leurs eaux calmes, loin de la foule.',
                    'actividades' => ['Plage Esmeralda', 'Lagunes Redonda et Limón', 'Montaña Redonda', 'Balades à cheval', 'Pêche artisanale'],
                ],
            ],
        ];

        foreach ($traducciones as $nombre => $idiomas) {
            Destino::where('nombre', $nombre)->first()?->update(['traducciones' => $idiomas]);
        }
    }
}
