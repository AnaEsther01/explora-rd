<?php

namespace Database\Seeders;

use App\Models\Solicitud;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Algunas solicitudes de prueba de asesoría y de renta de vehículo,
 * para ver cómo se ven en «Mi cuenta» y en el panel del administrador.
 */
class SolicitudSeeder extends Seeder
{
    public function run(): void
    {
        $maria = Usuario::where('correo', 'maria@correo.test')->firstOrFail();
        $john = Usuario::where('correo', 'john@correo.test')->firstOrFail();

        $solicitudes = [
            [
                'usuario_id' => $maria->id,
                'servicio' => 'asesoria',
                'lugar' => 'Samaná',
                'fecha_inicio' => now()->addDays(30)->toDateString(),
                'fecha_fin' => now()->addDays(35)->toDateString(),
                'cantidad_personas' => 3,
                'telefono' => '809-555-0201',
                'mensaje' => 'Viajo con mi mamá y mi hija. Queremos playa tranquila y ver las ballenas.',
                'estado' => 'pendiente',
            ],
            [
                'usuario_id' => $john->id,
                'servicio' => 'vehiculo',
                'lugar' => 'Aeropuerto de Punta Cana',
                'fecha_inicio' => now()->addDays(12)->toDateString(),
                'fecha_fin' => now()->addDays(16)->toDateString(),
                'cantidad_personas' => 2,
                'tipo_vehiculo' => 'jeepeta',
                'telefono' => '+1 555 0102',
                'mensaje' => 'Automatic transmission, please.',
                'estado' => 'atendida',
                'respuesta' => 'Te escribimos por WhatsApp con tres opciones de jeepeta automática.',
            ],
        ];

        // firstOrCreate: si ya existe la solicitud de ese turista para ese servicio, no la repite
        foreach ($solicitudes as $solicitud) {
            Solicitud::firstOrCreate(
                ['usuario_id' => $solicitud['usuario_id'], 'servicio' => $solicitud['servicio']],
                $solicitud
            );
        }
    }
}
