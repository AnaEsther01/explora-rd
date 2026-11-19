<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Usuario;
use App\Notifications\NuevaSolicitudNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

/**
 * Solicitudes del turista: pedir una asesoría o un vehículo, y cancelarla.
 */
class SolicitudController extends Controller
{
    /** Máximo de solicitudes pendientes a la vez (evita que se llene de solicitudes repetidas). */
    public const MAXIMO_PENDIENTES = 3;

    public function store(Request $request)
    {
        $esVehiculo = $request->input('servicio') === 'vehiculo';

        $datos = $request->validate([
            'servicio' => ['required', Rule::in(array_keys(Solicitud::SERVICIOS))],
            'lugar' => ['required', 'string', 'max:120'],
            'fecha_inicio' => ['required', 'date', 'after:today', 'before:+1 year'],
            'fecha_fin' => [$esVehiculo ? 'required' : 'nullable', 'date', 'after_or_equal:fecha_inicio', 'before:+1 year'],
            'cantidad_personas' => ['required', 'integer', 'min:1', 'max:50'],
            'tipo_vehiculo' => [$esVehiculo ? 'required' : 'nullable', Rule::in(array_keys(Solicitud::TIPOS_VEHICULO))],
            'telefono' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{7,30}$/'],
            'mensaje' => ['nullable', 'string', 'max:1000'],
        ], [
            'fecha_inicio.after' => __('Elige una fecha a partir de mañana.'),
            'fecha_inicio.before' => __('Solo se puede solicitar hasta un año antes.'),
            'fecha_fin.before' => __('Solo se puede solicitar hasta un año antes.'),
            'fecha_fin.after_or_equal' => __('Esta fecha debe ser igual o después de la primera.'),
            'telefono.regex' => __('Escribe un teléfono válido (solo números, espacios, +, - y paréntesis).'),
        ]);

        $turista = $request->user();

        if ($turista->solicitudes()->where('estado', 'pendiente')->count() >= self::MAXIMO_PENDIENTES) {
            return back()->withInput()->with('error', __('Ya tienes :max solicitudes pendientes. Espera a que te respondamos o cancela alguna en «Mi cuenta».', ['max' => self::MAXIMO_PENDIENTES]));
        }

        // La asesoría no usa tipo de vehículo
        if (! $esVehiculo) {
            $datos['tipo_vehiculo'] = null;
        }

        $solicitud = $turista->solicitudes()->create($datos + ['estado' => 'pendiente']);

        // Guardamos el teléfono en su perfil si todavía no tenía uno
        if (! $turista->telefono) {
            $turista->update(['telefono' => $datos['telefono']]);
        }

        // Aviso por correo a los administradores. Si el correo falla, la solicitud igual queda guardada.
        try {
            Notification::send(Usuario::where('rol', 'administrador')->where('activo', true)->get(), new NuevaSolicitudNotification($solicitud));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('turista.panel')
            ->with('exito', __('¡Listo! Recibimos tu solicitud de «:servicio». Te contactaremos pronto.', ['servicio' => $solicitud->nombreServicio()]));
    }

    /** El turista cancela su propia solicitud mientras esté pendiente. */
    public function cancelar(Request $request, Solicitud $solicitud)
    {
        abort_unless((int) $solicitud->usuario_id === (int) $request->user()->id, 403, 'Esta solicitud no es tuya.');

        if (! $solicitud->puedeCancelarla()) {
            return back()->with('error', __('Esta solicitud ya no se puede cancelar.'));
        }

        $solicitud->update(['estado' => 'cancelada']);

        return back()->with('exito', __('Cancelaste tu solicitud de «:servicio».', ['servicio' => $solicitud->nombreServicio()]));
    }
}
