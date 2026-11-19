<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * «Mi cuenta» del turista: sus datos y sus reservas.
 * (Hacer reservas nuevas desde la página llega en la Etapa 7.)
 */
class TuristaPanelController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();

        // Primero las próximas (de la más cercana a la más lejana), al final las que ya pasaron
        $reservas = $usuario->reservas()
            ->with('excursion.destino', 'excursion.agencia')
            ->orderByRaw('fecha < ?', [today()->toDateString()])
            ->orderBy('fecha')
            ->get();

        // Solicitudes de asesoría y de renta de vehículos: las más nuevas primero
        $solicitudes = $usuario->solicitudes()->latest()->get();

        // Excursiones de las que ya dejó reseña (para mostrar «Escribir» o «Ver mi reseña»)
        $resenadas = $usuario->resenas()->pluck('excursion_id')->all();

        // Excursiones guardadas con el corazón (solo las que siguen publicadas)
        $favoritas = $usuario->favoritas()
            ->publicas()
            ->conValoracion()
            ->with(['agencia', 'destino'])
            ->get();

        return view('panel.turista', compact('usuario', 'reservas', 'solicitudes', 'resenadas', 'favoritas'));
    }
}
