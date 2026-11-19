<?php

namespace App\Http\Controllers\Agencia;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Notifications\EstadoReservaNotification;
use Illuminate\Http\Request;

/**
 * «Reservas» del panel de la agencia: ver las solicitudes y confirmarlas o rechazarlas.
 * (Diagrama de flujo: «La agencia revisa la solicitud» → «¿Hay disponibilidad?»)
 */
class ReservaAgenciaController extends Controller
{
    /** Lista con pestañas por estado y buscador. */
    public function index(Request $request)
    {
        $agencia = $request->user();
        $estado = in_array($request->query('estado'), Reserva::ESTADOS, true) ? $request->query('estado') : null;
        $buscar = trim((string) $request->query('buscar'));

        $base = Reserva::whereHas('excursion', fn ($q) => $q->where('agencia_id', $agencia->id));

        // Cuántas hay de cada estado (para las pestañas)
        $conteo = (clone $base)->selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');

        $reservas = (clone $base)
            ->with('usuario', 'excursion')
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->whereHas('usuario', fn ($u) => $u->where('nombre', 'like', "%{$buscar}%")->orWhere('correo', 'like', "%{$buscar}%"))
                ->orWhereHas('excursion', fn ($e) => $e->where('titulo', 'like', "%{$buscar}%"))))
            // Primero las pendientes, luego por fecha más cercana
            ->orderByRaw("estado = 'pendiente' desc")
            ->orderBy('fecha')
            ->paginate(10)
            ->withQueryString();

        return view('agencia.reservas.index', compact('reservas', 'estado', 'buscar', 'conteo'));
    }

    /** Detalle de una reserva con los datos de contacto del turista. */
    public function show(Request $request, Reserva $reserva)
    {
        $this->asegurarDeLaAgencia($request, $reserva);
        $reserva->load('usuario', 'excursion.destino');

        return view('agencia.reservas.show', compact('reserva'));
    }

    /** «Confirma la reserva y avisa al turista». */
    public function confirmar(Request $request, Reserva $reserva)
    {
        return $this->cambiarEstado($request, $reserva, 'confirmada', 'Confirmaste la reserva de :cliente. Le avisamos por correo.');
    }

    /** «Rechaza la reserva y avisa al turista». */
    public function rechazar(Request $request, Reserva $reserva)
    {
        return $this->cambiarEstado($request, $reserva, 'cancelada', 'Rechazaste la reserva de :cliente. Le avisamos por correo.');
    }

    // ---------- Ayudas ----------

    private function cambiarEstado(Request $request, Reserva $reserva, string $nuevoEstado, string $mensaje)
    {
        $this->asegurarDeLaAgencia($request, $reserva);

        // Solo se responden las que siguen pendientes
        if (! $reserva->estaPendiente()) {
            return back()->with('error', 'Esta reserva ya fue respondida (está '.$reserva->estado.').');
        }

        $reserva->update(['estado' => $nuevoEstado]);

        // Aviso por correo al turista (si el correo falla, el cambio igual queda guardado)
        try {
            $reserva->usuario->notify(new EstadoReservaNotification($reserva));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('exito', str_replace(':cliente', $reserva->usuario->nombre, $mensaje));
    }

    /** Una agencia solo puede ver y responder reservas de SUS excursiones. */
    private function asegurarDeLaAgencia(Request $request, Reserva $reserva): void
    {
        abort_unless($reserva->esDeAgencia($request->user()), 403, 'Esta reserva no es de tu agencia.');
    }
}
