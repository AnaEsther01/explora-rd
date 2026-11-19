<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Resena;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * «Resumen» del panel de la agencia (boceto 19):
 * totales, visitas al perfil y reservas recientes con buscador.
 * (Confirmar o rechazar reservas llega en la Etapa 7.)
 */
class AgenciaPanelController extends Controller
{
    public function index(Request $request)
    {
        $agencia = $request->user();
        $buscar = trim((string) $request->query('buscar'));

        // Reservas de las excursiones de ESTA agencia
        $reservasDeAgencia = Reserva::whereHas('excursion', fn ($q) => $q->where('agencia_id', $agencia->id));

        $totales = [
            'reservas' => (clone $reservasDeAgencia)->count(),
            'pendientes' => (clone $reservasDeAgencia)->where('estado', 'pendiente')->count(),
            'excursiones' => $agencia->excursiones()->where('disponible', true)->count(),
            'visitas' => $agencia->visitas,
            // Cuántas veces los turistas guardaron sus excursiones con el corazón ♥
            'favoritos' => DB::table('favoritos')
                ->join('excursiones', 'excursiones.id', '=', 'favoritos.excursion_id')
                ->where('excursiones.agencia_id', $agencia->id)->count(),
            // Promedio de estrellas de todas sus excursiones
            'estrellas' => Resena::visibles()->whereHas('excursion', fn ($q) => $q->where('agencia_id', $agencia->id))->avg('estrellas'),
            'resenas' => Resena::visibles()->whereHas('excursion', fn ($q) => $q->where('agencia_id', $agencia->id))->count(),
        ];

        $recientes = (clone $reservasDeAgencia)
            ->with('usuario', 'excursion')
            // Buscador: por nombre del cliente o título de la excursión
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->whereHas('usuario', fn ($u) => $u->where('nombre', 'like', "%{$buscar}%"))
                ->orWhereHas('excursion', fn ($e) => $e->where('titulo', 'like', "%{$buscar}%"))))
            ->latest()
            ->take(8)
            ->get();

        // Consejos para completar el perfil
        $pendientesPerfil = array_filter([
            $agencia->tieneLogo() ? null : 'Sube el logotipo de tu agencia',
            $agencia->descripcion ? null : 'Escribe la presentación de tu agencia',
            count($agencia->fotosExistentes()) ? null : 'Agrega fotos de tus experiencias',
            $totales['excursiones'] ? null : 'Publica tu primera excursión',
        ]);

        return view('panel.agencia', compact('agencia', 'totales', 'recientes', 'buscar', 'pendientesPerfil'));
    }
}
