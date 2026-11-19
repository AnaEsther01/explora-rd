<?php

namespace App\Http\Controllers;

use App\Models\Destino;
use App\Models\Excursion;
use Illuminate\Http\Request;

/**
 * Catálogo de excursiones (con buscador y filtros) y ficha de cada excursión.
 */
class ExcursionController extends Controller
{
    /**
     * /excursiones — catálogo. Filtros que acepta la dirección:
     *   destino   = texto libre (busca en destino, provincia, título y ubicación)
     *   categoria = playas, montana, catamaran, cultura, gastronomia, familia, aventura
     *   tipo      = excursion, circuito o paquete (o «circuitos» = circuitos y paquetes)
     *   precio    = precio máximo por persona
     *   orden     = recomendados, precio_menor, precio_mayor, valoradas (mejor valoradas)
     *   fecha, personas = se guardan para pasarlos a la ficha (la reserva llega en la Etapa 7)
     */
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'destino' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'string', 'max:30'],
            'tipo' => ['nullable', 'string', 'max:20'],
            'precio' => ['nullable', 'integer', 'min:0'],
            'orden' => ['nullable', 'string', 'max:20'],
            'fecha' => ['nullable', 'date'],
            'personas' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $texto = trim($filtros['destino'] ?? '');
        $categoria = array_key_exists($filtros['categoria'] ?? '', Excursion::CATEGORIAS) ? $filtros['categoria'] : null;
        $tipo = $filtros['tipo'] ?? null;
        $orden = $filtros['orden'] ?? 'recomendados';

        $consulta = Excursion::with(['agencia', 'destino'])
            ->publicas()
            ->conValoracion() // promedio de estrellas para las tarjetas
            // Texto: busca en el destino, la provincia, el título o la ubicación
            ->when($texto !== '', function ($q) use ($texto) {
                $q->where(function ($q) use ($texto) {
                    $q->where('titulo', 'like', "%{$texto}%")
                        ->orWhere('ubicacion', 'like', "%{$texto}%")
                        ->orWhereHas('destino', fn ($d) => $d->where('nombre', 'like', "%{$texto}%")
                            ->orWhere('provincia', 'like', "%{$texto}%"));
                });
            })
            ->when($categoria, fn ($q) => $q->where('categoria', $categoria))
            ->when($tipo === 'circuitos', fn ($q) => $q->whereIn('tipo', ['circuito', 'paquete']))
            ->when(array_key_exists($tipo ?? '', Excursion::TIPOS), fn ($q) => $q->where('tipo', $tipo))
            ->when(! empty($filtros['precio']), fn ($q) => $q->where('precio', '<=', $filtros['precio']));

        match ($orden) {
            'precio_menor' => $consulta->orderBy('precio'),
            'precio_mayor' => $consulta->orderByDesc('precio'),
            'valoradas' => $consulta->orderByDesc('promedio_estrellas')->orderByDesc('total_resenas'),
            default => $consulta->orderBy('tipo')->orderBy('titulo'),
        };

        $excursiones = $consulta->paginate(9)->withQueryString();

        return view('excursiones.index', [
            'excursiones' => $excursiones,
            'filtros' => $filtros,
            'categoriaActual' => $categoria,
            'listaDestinos' => Destino::orderBy('nombre')->pluck('nombre'),
            'precioMaximo' => (int) ceil((float) Excursion::publicas()->max('precio')),
        ]);
    }

    /** /excursiones/{slug} — ficha completa de una excursión. */
    public function show(Request $request, Excursion $excursion)
    {
        // Una excursión no disponible no se muestra al público
        abort_unless($excursion->esPublica(), 404);

        $excursion->load(['agencia', 'destino']);

        // Fecha y personas que el turista eligió en el buscador (si las hay)
        $fecha = $request->date('fecha')?->format('Y-m-d');
        $personas = $request->integer('personas') ?: 2;

        // Reseñas: las visibles (las más nuevas primero) y cuántas hay de cada estrella
        $resenas = $excursion->resenas()->visibles()->with('usuario')->latest()->take(30)->get();
        $porEstrellas = $excursion->resenas()->visibles()
            ->selectRaw('estrellas, count(*) as total')->groupBy('estrellas')->pluck('total', 'estrellas');
        $usuario = $request->user();
        $miResena = $usuario ? $excursion->resenas()->where('usuario_id', $usuario->id)->first() : null;
        $puedeResenar = $excursion->puedeResenar($usuario);

        // Otras experiencias en el mismo destino o de la misma agencia
        $relacionadas = Excursion::with(['agencia', 'destino'])
            ->publicas()
            ->conValoracion()
            ->where('id', '!=', $excursion->id)
            ->where(fn ($q) => $q->where('destino_id', $excursion->destino_id)
                ->orWhere('agencia_id', $excursion->agencia_id))
            ->take(3)
            ->get();

        return view('excursiones.show', compact('excursion', 'fecha', 'personas', 'relacionadas',
            'resenas', 'porEstrellas', 'miResena', 'puedeResenar'));
    }
}
