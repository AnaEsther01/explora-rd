<?php

namespace App\Http\Controllers;

use App\Models\Excursion;
use App\Models\Reserva;
use App\Notifications\NuevaReservaNotification;
use Illuminate\Http\Request;

/**
 * Reservas del turista: solicitar una reserva y cancelarla.
 * (Diagrama de flujo del Paso 5: «Llena y envía la solicitud de reserva»)
 */
class ReservaController extends Controller
{
    /** El turista envía la solicitud desde la ficha de la excursión. */
    public function store(Request $request, Excursion $excursion)
    {
        abort_unless($excursion->esPublica(), 404);

        $datos = $request->validate([
            'fecha' => ['required', 'date', 'after:today', 'before:+1 year'],
            'cantidad_personas' => ['required', 'integer', 'min:1', 'max:50'],
            'comentario' => ['nullable', 'string', 'max:500'],
        ], [
            'fecha.after' => __('Elige una fecha a partir de mañana.'),
            'fecha.before' => __('Solo se puede reservar hasta un año antes.'),
        ]);

        $turista = $request->user();

        // No dejar dos solicitudes activas de la misma excursión para la misma fecha
        $repetida = $turista->reservas()
            ->where('excursion_id', $excursion->id)
            ->whereDate('fecha', $datos['fecha'])
            ->where('estado', '!=', 'cancelada')
            ->exists();

        if ($repetida) {
            return back()->withInput()->with('error', __('Ya tienes una reserva de esta excursión para esa fecha. Revísala en «Mi cuenta».'));
        }

        $reserva = $turista->reservas()->create([
            'excursion_id' => $excursion->id,
            'fecha' => $datos['fecha'],
            'cantidad_personas' => $datos['cantidad_personas'],
            'comentario' => $datos['comentario'] ?? null,
            'estado' => 'pendiente',
        ]);

        // Aviso por correo a la agencia. Si el correo falla, la reserva igual queda guardada
        // y el error se anota en storage/logs/laravel.log.
        try {
            $excursion->agencia->notify(new NuevaReservaNotification($reserva));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('turista.panel')
            ->with('exito', __('¡Listo! Tu solicitud para «:titulo» fue enviada. La agencia te confirmará pronto.', ['titulo' => $excursion->titulo]));
    }

    /** El turista cancela su propia reserva (si la fecha todavía no llega). */
    public function cancelar(Request $request, Reserva $reserva)
    {
        abort_unless((int) $reserva->usuario_id === (int) $request->user()->id, 403, 'Esta reserva no es tuya.');

        if (! $reserva->puedeCancelarla()) {
            return back()->with('error', __('Esta reserva ya no se puede cancelar.'));
        }

        $reserva->update(['estado' => 'cancelada']);

        return back()->with('exito', __('Cancelaste tu reserva de «:titulo».', ['titulo' => $reserva->excursion->titulo]));
    }
}
