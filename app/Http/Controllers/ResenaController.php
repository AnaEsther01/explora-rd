<?php

namespace App\Http\Controllers;

use App\Models\Excursion;
use Illuminate\Http\Request;

/**
 * Reseñas del turista: escribir (o editar) su opinión de una excursión y borrarla.
 */
class ResenaController extends Controller
{
    /** Guarda la reseña. Si ya tenía una de esta excursión, la actualiza. */
    public function store(Request $request, Excursion $excursion)
    {
        $turista = $request->user();

        // Solo quien ya hizo la excursión (reserva confirmada con fecha pasada)
        if (! $excursion->puedeResenar($turista)) {
            return back()->with('error', __('Podrás opinar cuando hayas hecho esta excursión con una reserva confirmada.'));
        }

        $datos = $request->validate([
            'estrellas' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string', 'max:600'],
        ], [
            'estrellas.required' => __('Elige de 1 a 5 estrellas.'),
        ]);

        $resena = $turista->resenas()->updateOrCreate(
            ['excursion_id' => $excursion->id],
            ['estrellas' => $datos['estrellas'], 'comentario' => $datos['comentario'] ?? null]
        );

        $mensaje = $resena->wasRecentlyCreated ? __('¡Gracias por tu reseña!') : __('Actualizamos tu reseña.');

        return redirect()->to(route('excursiones.show', $excursion).'#resenas')->with('exito', $mensaje);
    }

    /** El turista borra su propia reseña. */
    public function destroy(Request $request, Excursion $excursion)
    {
        $request->user()->resenas()->where('excursion_id', $excursion->id)->delete();

        return redirect()->to(route('excursiones.show', $excursion).'#resenas')->with('exito', __('Borraste tu reseña.'));
    }
}
