<?php

namespace App\Http\Controllers;

use App\Models\Excursion;
use Illuminate\Http\Request;

/**
 * Favoritos del turista: el corazón ♥ guarda o quita una excursión.
 *
 * El botón funciona de dos maneras:
 *  - Con JavaScript (public/js/app.js): la página no se recarga y recibe una respuesta JSON.
 *  - Sin JavaScript: es un formulario normal y vuelve a la misma página.
 */
class FavoritoController extends Controller
{
    public function alternar(Request $request, Excursion $excursion)
    {
        $turista = $request->user();
        $yaEra = $turista->favoritas()->whereKey($excursion->id)->exists();

        // Una excursión oculta no se puede guardar (pero sí quitar, si ya estaba guardada)
        abort_unless($yaEra || $excursion->esPublica(), 404);

        $turista->favoritas()->toggle($excursion->id);
        $turista->olvidarFavoritas();

        $esFavorita = ! $yaEra;
        $mensaje = $esFavorita
            ? __('Guardaste «:titulo» en tus favoritos.', ['titulo' => $excursion->titulo])
            : __('Quitaste «:titulo» de tus favoritos.', ['titulo' => $excursion->titulo]);

        if ($request->expectsJson()) {
            return response()->json([
                'favorita' => $esFavorita,
                'mensaje' => $mensaje,
                'total' => count($turista->idsFavoritas()),
            ]);
        }

        return back()->with('exito', $mensaje);
    }
}
