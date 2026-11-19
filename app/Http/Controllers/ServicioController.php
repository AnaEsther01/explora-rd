<?php

namespace App\Http\Controllers;

use App\Models\Destino;
use Illuminate\Http\Request;

/**
 * Páginas de los dos servicios especiales (sección 6 del documento del proyecto):
 *   /asesorias          → orientación para organizar el viaje
 *   /renta-de-vehiculos → vehículos disponibles y empresas de alquiler
 *
 * Si el turista viene del buscador de la portada, el formulario llega
 * con el lugar, la fecha y las personas ya escritos.
 */
class ServicioController extends Controller
{
    /** Aeropuertos internacionales del país: lugares comunes para recoger un vehículo. */
    public const AEROPUERTOS = [
        'Aeropuerto de Punta Cana',
        'Aeropuerto Las Américas (Santo Domingo)',
        'Aeropuerto del Cibao (Santiago)',
        'Aeropuerto de Puerto Plata',
        'Aeropuerto El Catey (Samaná)',
        'Aeropuerto de La Romana',
    ];

    public function asesorias(Request $request)
    {
        return view('servicios.asesorias', [
            'previo' => $this->datosPrevios($request),
            'lugares' => Destino::orderBy('nombre')->pluck('nombre'),
        ]);
    }

    public function vehiculos(Request $request)
    {
        return view('servicios.vehiculos', [
            'previo' => $this->datosPrevios($request),
            'lugares' => collect(self::AEROPUERTOS)->merge(Destino::orderBy('nombre')->pluck('nombre')),
        ]);
    }

    /** Lo que el turista escribió en el buscador de la portada (si viene de allí). */
    private function datosPrevios(Request $request): array
    {
        $fecha = $request->query('fecha');
        $valida = is_string($fecha) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) && $fecha > today()->toDateString();

        return [
            'lugar' => mb_substr(trim((string) $request->query('lugar', '')), 0, 120),
            'fecha' => $valida ? $fecha : null,
            'personas' => min(max($request->integer('personas', 2), 1), 50),
        ];
    }
}
