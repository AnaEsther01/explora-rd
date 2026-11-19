<?php

namespace Database\Seeders;

use App\Models\Destino;
use Illuminate\Database\Seeder;

/**
 * Ubicación aproximada (latitud, longitud) de cada destino para el mapa.
 * Se buscan por su slug; si un destino no existe, se salta.
 */
class UbicacionDestinoSeeder extends Seeder
{
    public const UBICACIONES = [
        'jarabacoa' => [19.1167, -70.6333],
        'bahia-de-las-aguilas' => [17.8383, -71.6531],
        'punta-cana' => [18.5601, -68.3725],
        'santo-domingo' => [18.4733, -69.8836], // Zona Colonial
        'samana' => [19.2056, -69.3365],
        'puerto-plata' => [19.7934, -70.6884],
        'la-romana' => [18.4273, -68.9728],
        'barahona' => [18.2085, -71.1008],
        'constanza' => [18.9097, -70.7450],
        'montecristi' => [19.8473, -71.6461],
        'santiago' => [19.4517, -70.6970],
        'miches' => [18.9833, -69.0500],
    ];

    public function run(): void
    {
        foreach (self::UBICACIONES as $slug => [$latitud, $longitud]) {
            Destino::where('slug', $slug)->update(['latitud' => $latitud, 'longitud' => $longitud]);
        }
    }
}
