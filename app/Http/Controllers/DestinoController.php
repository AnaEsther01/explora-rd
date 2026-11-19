<?php

namespace App\Http\Controllers;

use App\Models\Destino;
use Illuminate\Http\Request;

/**
 * Páginas de destinos: la lista de todos y la página de cada uno.
 */
class DestinoController extends Controller
{
    /** /destinos — todos los destinos, con filtro por tipo (playa, montaña, ciudad). */
    public function index(Request $request)
    {
        $tipo = $request->query('tipo');

        $destinos = Destino::withCount(['excursiones' => fn ($q) => $q->publicas()])
            ->when(array_key_exists($tipo, Destino::TIPOS), fn ($q) => $q->where('tipo', $tipo))
            ->orderBy('nombre')
            ->get();

        return view('destinos.index', [
            'destinos' => $destinos,
            'tipoActual' => array_key_exists($tipo, Destino::TIPOS) ? $tipo : null,
        ]);
    }

    /** /destinos/{slug} — página de un destino con sus actividades y excursiones. */
    public function show(Destino $destino)
    {
        $excursiones = $destino->excursiones()
            ->with(['agencia', 'destino'])
            ->publicas()
            ->conValoracion() // promedio de estrellas para las tarjetas
            ->orderBy('precio')
            ->get();

        // Otros destinos del mismo tipo para seguir explorando
        $otros = Destino::where('id', '!=', $destino->id)
            ->orderByRaw('tipo = ? desc', [$destino->tipo])
            ->orderBy('nombre')
            ->take(4)
            ->get();

        return view('destinos.show', compact('destino', 'excursiones', 'otros'));
    }
}
