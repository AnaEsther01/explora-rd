<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resena;
use Illuminate\Http\Request;

/**
 * El administrador revisa las reseñas: puede ocultar las ofensivas o falsas, o borrarlas.
 */
class ResenaAdminController extends Controller
{
    public function index(Request $request)
    {
        $estado = in_array($request->query('estado'), ['visibles', 'ocultas'], true) ? $request->query('estado') : null;
        $estrellas = in_array($request->integer('estrellas'), [1, 2, 3, 4, 5], true) ? $request->integer('estrellas') : null;

        $resenas = Resena::with(['usuario', 'excursion.agencia'])
            ->when($estado === 'visibles', fn ($q) => $q->where('visible', true))
            ->when($estado === 'ocultas', fn ($q) => $q->where('visible', false))
            ->when($estrellas, fn ($q) => $q->where('estrellas', $estrellas))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.resenas.index', compact('resenas', 'estado', 'estrellas'));
    }

    /** Mostrar u ocultar una reseña. */
    public function cambiarVisible(Resena $resena)
    {
        $resena->update(['visible' => ! $resena->visible]);

        return back()->with('exito', $resena->visible ? 'La reseña se muestra otra vez.' : 'La reseña quedó oculta.');
    }

    public function destroy(Resena $resena)
    {
        $resena->delete();

        return back()->with('exito', 'Reseña borrada.');
    }
}
