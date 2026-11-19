<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * El administrador atiende las solicitudes de asesoría y de renta de vehículos:
 * las ve, cambia su estado y le deja una respuesta al turista.
 */
class SolicitudAdminController extends Controller
{
    public function index(Request $request)
    {
        $estado = in_array($request->query('estado'), Solicitud::ESTADOS, true) ? $request->query('estado') : null;
        $servicio = array_key_exists((string) $request->query('servicio'), Solicitud::SERVICIOS) ? $request->query('servicio') : null;

        $solicitudes = Solicitud::with('usuario')
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($servicio, fn ($q) => $q->where('servicio', $servicio))
            ->orderByRaw("estado = 'pendiente' desc") // primero las pendientes
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.solicitudes.index', compact('solicitudes', 'estado', 'servicio'));
    }

    /** Cambiar el estado y escribir una respuesta para el turista. */
    public function update(Request $request, Solicitud $solicitud)
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in(Solicitud::ESTADOS)],
            'respuesta' => ['nullable', 'string', 'max:1000'],
        ]);

        $solicitud->update($datos);

        return back()->with('exito', 'Solicitud #'.$solicitud->id.' actualizada.');
    }
}
