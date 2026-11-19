<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Destino;
use App\Models\Excursion;
use App\Models\Reserva;
use App\Models\Solicitud;
use App\Models\Usuario;
use Illuminate\Http\Request;

/**
 * Panel del administrador: por ahora un resumen general de la plataforma.
 */
class AdminPanelController extends Controller
{
    public function index()
    {
        $totales = [
            'turistas' => Usuario::where('rol', 'turista')->count(),
            'agencias' => Usuario::where('rol', 'agencia')->count(),
            'destinos' => Destino::count(),
            'excursiones' => Excursion::count(),
            'reservas' => Reserva::count(),
            'pendientes' => Reserva::where('estado', 'pendiente')->count(),
            'suspendidos' => Usuario::where('activo', false)->count(),
            'solicitudes' => Solicitud::where('estado', 'pendiente')->count(),
        ];

        $usuariosRecientes = Usuario::latest()->take(6)->get();

        return view('panel.admin', compact('totales', 'usuariosRecientes'));
    }

    /** Supervisar todas las reservas de la plataforma (solo lectura). */
    public function reservas(Request $request)
    {
        $estado = in_array($request->query('estado'), Reserva::ESTADOS, true) ? $request->query('estado') : null;
        $agenciaId = $request->integer('agencia') ?: null;

        $reservas = Reserva::with('usuario', 'excursion.agencia')
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($agenciaId, fn ($q) => $q->whereHas('excursion', fn ($e) => $e->where('agencia_id', $agenciaId)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $agencias = Usuario::where('rol', 'agencia')->orderBy('nombre_comercial')->get();

        return view('panel.admin-reservas', compact('reservas', 'estado', 'agenciaId', 'agencias'));
    }
}
