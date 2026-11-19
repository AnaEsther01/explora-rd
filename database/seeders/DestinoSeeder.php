<?php

namespace Database\Seeders;

use App\Models\Destino;
use Illuminate\Database\Seeder;

/**
 * Destinos turísticos de República Dominicana con sus actividades.
 * Las fotos están en public/img/destinos (créditos en public/img/CREDITOS.txt).
 */
class DestinoSeeder extends Seeder
{
    public function run(): void
    {
        $destinos = [
            [
                'nombre' => 'Jarabacoa',
                'provincia' => 'La Vega',
                'descripcion' => 'Montañas, ríos y naturaleza. Conocida como «la ciudad de la eterna primavera», Jarabacoa tiene un clima fresco todo el año, ríos de aguas cristalinas y las cascadas más famosas del país. Es el lugar perfecto para la aventura y el ecoturismo en plena cordillera Central.',
                'actividades' => ['Rafting en el río Yaque del Norte', 'Salto de Jimenoa y Salto de Baiguate', 'Parapente sobre el valle', 'Senderismo y cabalgatas', 'Subida al pico Duarte (punto de partida)'],
                'tipo' => 'montaña',
                'foto' => 'img/destinos/jarabacoa.jpg',
            ],
            [
                'nombre' => 'Bahía de las Águilas',
                'provincia' => 'Pedernales',
                'descripcion' => 'Playas y paisajes naturales. Considerada una de las playas más vírgenes del Caribe, está dentro del Parque Nacional Jaragua. Ocho kilómetros de arena blanca y agua turquesa, sin hoteles ni construcciones, a los que se llega en bote o en vehículo todoterreno.',
                'actividades' => ['Paseo en bote desde Cabo Rojo', 'Snorkel en aguas cristalinas', 'Visita a la laguna de Oviedo', 'Observación de iguanas y aves', 'Acampada en zonas permitidas'],
                'tipo' => 'playa',
                'foto' => 'img/destinos/bahia-de-las-aguilas.jpg',
            ],
            [
                'nombre' => 'Punta Cana',
                'provincia' => 'La Altagracia',
                'descripcion' => 'Playas y actividades acuáticas. El destino más visitado del país: kilómetros de playas de arena blanca bordeadas de cocoteros, aguas tranquilas y una gran oferta de excursiones, desde catamaranes hasta aventuras en buggy.',
                'actividades' => ['Catamarán a isla Saona', 'Buggies y playa Macao', 'Snorkel y buceo en arrecifes', 'Hoyo Azul y Scape Park', 'Pesca deportiva'],
                'tipo' => 'playa',
                'foto' => 'img/destinos/punta-cana.jpg',
            ],
            [
                'nombre' => 'Santo Domingo',
                'provincia' => 'Distrito Nacional',
                'descripcion' => 'Historia, cultura y gastronomía. La primera ciudad fundada por europeos en América. Su Zona Colonial, Patrimonio de la Humanidad, guarda la primera catedral del continente, calles de piedra llenas de museos, plazas, restaurantes y vida nocturna.',
                'actividades' => ['Recorrido por la Zona Colonial', 'Catedral Primada de América', 'Alcázar de Colón y Museo de las Casas Reales', 'Ruta gastronómica dominicana', 'Los Tres Ojos y el Malecón'],
                'tipo' => 'ciudad',
                'foto' => 'img/destinos/santo-domingo.jpg',
            ],
            [
                'nombre' => 'Samaná',
                'provincia' => 'Samaná',
                'descripcion' => 'Playas, cascadas y ballenas. Una península de naturaleza exuberante con algunas de las playas más bellas del país, como Rincón y Frontón. De enero a marzo llegan miles de ballenas jorobadas a su bahía.',
                'actividades' => ['Avistamiento de ballenas (enero a marzo)', 'Cascada El Limón', 'Cayo Levantado', 'Playa Rincón y playa Frontón', 'Parque Nacional Los Haitises'],
                'tipo' => 'playa',
                'foto' => 'img/destinos/samana.jpg',
            ],
            [
                'nombre' => 'Puerto Plata',
                'provincia' => 'Puerto Plata',
                'descripcion' => 'Playas, teleférico y aventura. La «Novia del Atlántico» combina playas como Sosúa y Cabarete con el único teleférico del Caribe, que sube a la loma Isabel de Torres, y los famosos 27 Charcos de Damajagua.',
                'actividades' => ['Teleférico a la loma Isabel de Torres', '27 Charcos de Damajagua', 'Kitesurf en Cabarete', 'Playa Sosúa', 'Fortaleza San Felipe'],
                'tipo' => 'playa',
                'foto' => 'img/destinos/puerto-plata.jpg',
            ],
            [
                'nombre' => 'La Romana',
                'provincia' => 'La Romana',
                'descripcion' => 'Playas, buceo e islas. Desde el pueblo de pescadores de Bayahíbe salen los paseos a isla Saona e isla Catalina. Muy cerca está Altos de Chavón, una villa de piedra de estilo mediterráneo con vista al río Chavón.',
                'actividades' => ['Isla Catalina e isla Saona', 'Buceo en el Parque Nacional del Este', 'Altos de Chavón', 'Playa Dominicus', 'Cueva de las Maravillas'],
                'tipo' => 'playa',
                'foto' => 'img/destinos/la-romana.jpg',
            ],
            [
                'nombre' => 'Barahona',
                'provincia' => 'Barahona',
                'descripcion' => 'Donde la montaña se encuentra con el mar. La costa de Barahona tiene playas de piedra con aguas turquesas, ríos que bajan de la sierra hasta el mar, el curioso Polo Magnético y las minas de larimar, la piedra nacional.',
                'actividades' => ['Balneario de San Rafael', 'Polo Magnético', 'Minas de larimar', 'Playa Los Patos', 'Lago Enriquillo'],
                'tipo' => 'playa',
                'foto' => 'img/destinos/barahona.jpg',
            ],
            [
                'nombre' => 'Constanza',
                'provincia' => 'La Vega',
                'descripcion' => 'Montañas, frío y campos de flores. El valle más alto del Caribe, a más de 1,200 metros sobre el nivel del mar. Clima fresco, cultivos de fresas y flores, cabañas de montaña y la impresionante cascada Aguas Blancas.',
                'actividades' => ['Cascada Aguas Blancas', 'Valle Nuevo y las Pirámides', 'Campos de flores y fresas', 'Cabañas de montaña', 'Senderismo'],
                'tipo' => 'montaña',
                'foto' => 'img/destinos/constanza.jpg',
            ],
            [
                'nombre' => 'Montecristi',
                'provincia' => 'Monte Cristi',
                'descripcion' => 'Naturaleza y paisajes únicos. En el extremo noroeste del país, Montecristi sorprende con El Morro, una montaña de mesa frente al mar, los cayos Siete Hermanos, manglares y salinas.',
                'actividades' => ['Subida a El Morro', 'Cayos Siete Hermanos', 'Paseo por los manglares', 'Salinas de Montecristi', 'Reloj público del centro histórico'],
                'tipo' => 'playa',
                'foto' => 'img/destinos/montecristi.jpg',
            ],
            [
                'nombre' => 'Santiago',
                'provincia' => 'Santiago',
                'descripcion' => 'Cultura, carnaval y tabaco. La «Ciudad Corazón» es la segunda ciudad del país. Tiene el Monumento a los Héroes de la Restauración, el Centro León, fábricas de cigarros y uno de los carnavales más coloridos del Caribe.',
                'actividades' => ['Monumento a los Héroes de la Restauración', 'Centro León', 'Ruta del tabaco', 'Carnaval de Santiago (febrero)', 'Gastronomía cibaeña'],
                'tipo' => 'ciudad',
                'foto' => 'img/destinos/santiago.jpg',
            ],
            [
                'nombre' => 'Miches',
                'provincia' => 'El Seibo',
                'descripcion' => 'Playas vírgenes y tranquilidad. Uno de los secretos mejor guardados del este: playa Esmeralda y Costa Esmeralda, con arena dorada, cocoteros y aguas tranquilas, lejos de las multitudes.',
                'actividades' => ['Playa Esmeralda', 'Laguna Redonda y laguna Limón', 'Montaña Redonda', 'Paseos a caballo', 'Pesca artesanal'],
                'tipo' => 'playa',
                'foto' => 'img/destinos/miches.jpg',
            ],
        ];

        // updateOrCreate: si el destino ya existe (mismo nombre) lo actualiza;
        // si no existe, lo crea. Así se puede volver a ejecutar sin duplicar.
        foreach ($destinos as $destino) {
            Destino::updateOrCreate(['nombre' => $destino['nombre']], $destino);
        }
    }
}
