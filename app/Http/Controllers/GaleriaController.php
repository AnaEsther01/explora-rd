<?php

namespace App\Http\Controllers;

use App\Models\Destino;
use App\Models\Excursion;
use App\Models\Usuario;
use App\Support\Creditos;

/**
 * /galeria — todas las fotos de Explora RD en un solo lugar:
 * las de los destinos, las de las excursiones y las que suben las agencias.
 * Solo salen las fotos que existen de verdad en public/.
 */
class GaleriaController extends Controller
{
    public function __invoke()
    {
        $fotos = collect();
        $existe = fn (?string $ruta) => $ruta && file_exists(public_path($ruta));

        // 1. Destinos
        foreach (Destino::orderBy('nombre')->get() as $destino) {
            if ($existe($destino->foto)) {
                $fotos->push($this->foto($destino->foto, $destino->nombre, $destino->provincia, $destino->tipo, 'destino', route('destinos.show', $destino)));
            }
        }

        // 2. Excursiones (solo las publicadas)
        foreach (Excursion::publicas()->with('destino')->orderBy('titulo')->get() as $excursion) {
            if ($existe($excursion->foto)) {
                $fotos->push($this->foto($excursion->foto, $excursion->titulo, $excursion->destino->nombre, $excursion->destino->tipo, 'excursion', route('excursiones.show', $excursion)));
            }
        }

        // 3. Fotos que suben las agencias en su perfil
        foreach (Usuario::agenciasPublicas()->orderBy('nombre_comercial')->get() as $agencia) {
            foreach ($agencia->fotosExistentes() as $ruta) {
                $fotos->push($this->foto($ruta, $agencia->nombreVisible(), __('Foto de la agencia'), null, 'agencia', route('agencias.show', $agencia)));
            }
        }

        // Si una agencia subió la misma foto que un destino o una excursión, sale una sola vez
        $fotos = $fotos->unique('src')->values();

        return view('galeria', compact('fotos'));
    }

    /** Los datos de una foto para la vista (y para el visor en grande). */
    private function foto(string $ruta, string $titulo, ?string $lugar, ?string $tipo, string $grupo, string $enlace): array
    {
        return [
            'src' => asset($ruta),
            'titulo' => $titulo,
            'lugar' => $lugar,
            'tipo' => $tipo,       // playa, montaña o ciudad (para el filtro)
            'grupo' => $grupo,     // destino, excursion o agencia
            'enlace' => $enlace,
            'credito' => Creditos::de($ruta),
        ];
    }
}
