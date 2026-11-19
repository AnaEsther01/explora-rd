<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Cuentas de prueba: 1 administrador, 2 agencias y 2 turistas.
 * Todas usan la misma contraseña de prueba: «password».
 * (Solo para trabajar en tu computadora; no usar en la página publicada).
 *
 * updateOrCreate: si la cuenta ya existe (mismo correo) la actualiza; si no, la crea.
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $cuentas = [
            [
                'nombre' => 'Administrador Explora RD',
                'correo' => 'admin@explorard.test',
                'rol' => 'administrador',
            ],
            [
                'nombre' => 'Luis Peña',
                'correo' => 'contacto@aventurasjarabacoa.test',
                'telefono' => '809-555-0101',
                'rol' => 'agencia',
                'nombre_comercial' => 'Aventuras Jarabacoa',
                'descripcion' => 'Agencia especializada en turismo de montaña y aventura: rafting, cascadas, senderismo y parapente en el corazón de la cordillera Central.',
                'whatsapp' => '18095550101',
                'direccion' => 'Calle Duarte #12, Jarabacoa, La Vega',
                'fotos' => ['img/destinos/jarabacoa.jpg', 'img/destinos/constanza.jpg'],
            ],
            [
                'nombre' => 'Carmen Rosario',
                'correo' => 'info@brisatropical.test',
                'telefono' => '809-555-0202',
                'rol' => 'agencia',
                'nombre_comercial' => 'Brisa Tropical Tours',
                'descripcion' => 'Excursiones de playa, paseos en catamarán y recorridos culturales por Punta Cana, Samaná y Santo Domingo.',
                'whatsapp' => '18095550202',
                'direccion' => 'Av. Barceló, Bávaro, La Altagracia',
                'fotos' => ['img/destinos/punta-cana.jpg', 'img/excursiones/catamaran-saona.jpg', 'img/destinos/santo-domingo.jpg'],
            ],
            [
                'nombre' => 'María Gómez',
                'correo' => 'maria@correo.test',
                'telefono' => '829-555-0303',
                'rol' => 'turista',
            ],
            [
                'nombre' => 'John Smith',
                'correo' => 'john@correo.test',
                'telefono' => '+1 305-555-0404',
                'rol' => 'turista',
            ],
        ];

        foreach ($cuentas as $cuenta) {
            Usuario::updateOrCreate(
                ['correo' => $cuenta['correo']],
                $cuenta + ['contrasena' => 'password']
            );
        }
    }
}
