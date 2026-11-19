<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destino;
use App\Models\Excursion;
use App\Models\Usuario;
use App\Support\Fotos;
use Illuminate\Http\Request;

/**
 * El administrador modera las excursiones de TODAS las agencias:
 * puede ocultarlas (por ejemplo, si tienen información falsa o inapropiada) o borrarlas.
 * El contenido lo escribe cada agencia desde su panel.
 */
class ExcursionAdminController extends Controller
{
    public function index(Request $request)
    {
        $agenciaId = $request->integer('agencia') ?: null;
        $destinoId = $request->integer('destino') ?: null;
        $estado = in_array($request->query('estado'), ['visibles', 'ocultas'], true) ? $request->query('estado') : null;
        $buscar = trim((string) $request->query('buscar'));

        $excursiones = Excursion::with(['agencia', 'destino'])
            ->withCount('reservas')
            ->when($agenciaId, fn ($q) => $q->where('agencia_id', $agenciaId))
            ->when($destinoId, fn ($q) => $q->where('destino_id', $destinoId))
            ->when($estado === 'visibles', fn ($q) => $q->where('disponible', true))
            ->when($estado === 'ocultas', fn ($q) => $q->where('disponible', false))
            ->when($buscar !== '', fn ($q) => $q->where('titulo', 'like', "%{$buscar}%"))
            ->orderBy('titulo')
            ->paginate(15)
            ->withQueryString();

        return view('admin.excursiones.index', [
            'excursiones' => $excursiones,
            'agencias' => Usuario::where('rol', 'agencia')->orderBy('nombre_comercial')->get(),
            'destinos' => Destino::orderBy('nombre')->get(),
            'agenciaId' => $agenciaId,
            'destinoId' => $destinoId,
            'estado' => $estado,
            'buscar' => $buscar,
        ]);
    }

    /** Ocultar / volver a mostrar una excursión. */
    public function cambiarDisponible(Excursion $excursion)
    {
        $excursion->update(['disponible' => ! $excursion->disponible]);

        return back()->with('exito', '«'.$excursion->titulo.'» '.($excursion->disponible ? 'ahora está visible.' : 'quedó oculta.'));
    }

    public function destroy(Excursion $excursion)
    {
        if ($excursion->reservas()->exists()) {
            return back()->with('error', 'No se puede borrar «'.$excursion->titulo.'» porque tiene reservas. Puedes ocultarla.');
        }

        Fotos::borrar($excursion->foto);
        $titulo = $excursion->titulo;
        $excursion->delete();

        return back()->with('exito', 'Se borró «'.$titulo.'».');
    }
}
