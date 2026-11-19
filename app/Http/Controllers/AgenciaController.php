<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

/**
 * Páginas públicas de las agencias: la lista y el perfil de cada una.
 */
class AgenciaController extends Controller
{
    /** /agencias — todas las agencias con cuántas excursiones tienen. */
    public function index()
    {
        $agencias = Usuario::agenciasPublicas()
            ->withCount(['excursiones' => fn ($q) => $q->publicas()])
            ->orderByDesc('excursiones_count')
            ->orderBy('nombre_comercial')
            ->get();

        return view('agencias.index', compact('agencias'));
    }

    /** /agencias/{id} — perfil público: presentación, fotos, videos, contacto y catálogo. */
    public function show(Request $request, Usuario $agencia)
    {
        // Solo las cuentas de agencia tienen perfil público
        abort_unless($agencia->esAgencia() && $agencia->activo, 404); // suspendidas: no se muestran

        // Cuenta la visita (no cuenta cuando la propia agencia mira su perfil)
        if ($request->user()?->id !== $agencia->id) {
            $agencia->increment('visitas');
        }

        $excursiones = $agencia->excursiones()
            ->with(['agencia', 'destino'])
            ->publicas()
            ->conValoracion() // promedio de estrellas para las tarjetas
            ->orderBy('tipo')
            ->orderBy('precio')
            ->get();

        return view('agencias.show', compact('agencia', 'excursiones'));
    }
}
