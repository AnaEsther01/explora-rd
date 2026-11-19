<?php

namespace App\Support;

use App\Models\Destino;
use App\Models\Excursion;
use App\Models\Resena;
use App\Models\Reserva;
use App\Models\Solicitud;
use App\Models\Usuario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Calcula los números de Explora RD para el reporte que hace Python.
 *
 * Laravel prepara los datos (sabe leer la base de datos) y los guarda en un archivo JSON;
 * después el programa de Python (python/reporte.py) lee ese archivo y dibuja las gráficas.
 * Así Python no necesita conectarse a MySQL.
 *
 * Los cálculos se hacen en PHP (no con funciones especiales de MySQL)
 * para que funcionen igual en MySQL y en la base de datos de las pruebas.
 */
class Estadisticas
{
    public static function calcular(): array
    {
        $reservas = Reserva::with('excursion.agencia', 'excursion.destino')->get();
        $activas = $reservas->where('estado', '!=', 'cancelada');
        $confirmadas = $reservas->where('estado', 'confirmada');

        return [
            'generado' => now()->format('d/m/Y h:i A'),
            'totales' => [
                'turistas' => Usuario::where('rol', 'turista')->count(),
                'agencias' => Usuario::agenciasPublicas()->count(),
                'destinos' => Destino::count(),
                'excursiones' => Excursion::publicas()->count(),
                'reservas' => $reservas->count(),
                'personas' => (int) $activas->sum('cantidad_personas'),
                'ingresos_estimados' => round($confirmadas->sum(fn ($r) => $r->total()), 2),
                'solicitudes' => Solicitud::count(),
                'solicitudes_pendientes' => Solicitud::where('estado', 'pendiente')->count(),
                'resenas' => Resena::visibles()->count(),
                'promedio_estrellas' => round((float) Resena::visibles()->avg('estrellas'), 2),
                'favoritos' => DB::table('favoritos')->count(),
            ],
            'reservas_por_estado' => collect(Reserva::ESTADOS)
                ->mapWithKeys(fn ($e) => [$e => $reservas->where('estado', $e)->count()])->all(),
            'reservas_por_mes' => self::reservasPorMes($activas),
            'mas_reservadas' => self::masReservadas($activas),
            'mejor_valoradas' => self::mejorValoradas(),
            'destinos' => self::destinos($activas),
            'agencias' => self::agencias($reservas),
            'solicitudes_por_servicio' => collect(Solicitud::SERVICIOS)
                ->mapWithKeys(fn ($s, $clave) => [$s['nombre'] => Solicitud::where('servicio', $clave)->count()])->all(),
        ];
    }

    /** Viajes reservados por mes (según la fecha de la excursión): 5 meses atrás y 6 adelante. */
    private static function reservasPorMes($activas): array
    {
        $meses = [];
        $nombres = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        for ($i = -5; $i <= 6; $i++) {
            $mes = now()->startOfMonth()->addMonths($i);
            $delMes = $activas->filter(fn ($r) => $r->fecha->format('Y-m') === $mes->format('Y-m'));
            $meses[] = [
                'mes' => $nombres[$mes->month - 1].' '.$mes->format('Y'),
                'reservas' => $delMes->count(),
                'personas' => (int) $delMes->sum('cantidad_personas'),
            ];
        }

        return $meses;
    }

    /** Las 5 excursiones con más reservas (sin contar las canceladas). */
    private static function masReservadas($activas): array
    {
        return $activas->groupBy('excursion_id')
            ->map(fn ($grupo) => [
                'titulo' => $grupo->first()->excursion->titulo,
                'agencia' => $grupo->first()->excursion->agencia->nombreVisible(),
                'reservas' => $grupo->count(),
                'personas' => (int) $grupo->sum('cantidad_personas'),
            ])
            ->sortByDesc('reservas')->take(5)->values()->all();
    }

    /** Las 5 excursiones con mejor promedio de estrellas. */
    private static function mejorValoradas(): array
    {
        return Excursion::conValoracion()->with('agencia')->get()
            ->filter(fn ($e) => $e->totalResenas() > 0)
            ->sortBy([['promedio_estrellas', 'desc'], ['total_resenas', 'desc']])
            ->take(5)
            ->map(fn ($e) => [
                'titulo' => $e->titulo,
                'agencia' => $e->agencia->nombreVisible(),
                'promedio' => $e->promedioEstrellas(),
                'resenas' => $e->totalResenas(),
            ])->values()->all();
    }

    /** Cada destino: cuántas veces guardaron sus excursiones en favoritos y cuántas reservas tiene. */
    private static function destinos($activas): array
    {
        $favoritos = DB::table('favoritos')
            ->join('excursiones', 'excursiones.id', '=', 'favoritos.excursion_id')
            ->select('excursiones.destino_id', DB::raw('count(*) as total'))
            ->groupBy('excursiones.destino_id')
            ->pluck('total', 'destino_id');

        return Destino::orderBy('nombre')->get()
            ->map(fn ($d) => [
                'nombre' => $d->nombre,
                'tipo' => $d->tipo,
                'favoritos' => (int) ($favoritos[$d->id] ?? 0),
                'reservas' => $activas->filter(fn ($r) => $r->excursion->destino_id === $d->id)->count(),
            ])
            ->filter(fn ($d) => $d['favoritos'] || $d['reservas'])
            ->sortByDesc(fn ($d) => $d['favoritos'] + $d['reservas'])
            ->values()->all();
    }

    /** Resumen de cada agencia activa. */
    private static function agencias($reservas): array
    {
        return Usuario::agenciasPublicas()->get()
            ->map(function ($agencia) use ($reservas) {
                $suyas = $reservas->filter(fn ($r) => $r->excursion->agencia_id === $agencia->id);
                $promedio = Resena::visibles()->whereHas('excursion', fn ($q) => $q->where('agencia_id', $agencia->id))->avg('estrellas');

                return [
                    'nombre' => $agencia->nombreVisible(),
                    'excursiones' => $agencia->excursiones()->where('disponible', true)->count(),
                    'reservas' => $suyas->where('estado', '!=', 'cancelada')->count(),
                    'confirmadas' => $suyas->where('estado', 'confirmada')->count(),
                    'ingresos_estimados' => round($suyas->where('estado', 'confirmada')->sum(fn ($r) => $r->total()), 2),
                    'promedio_estrellas' => $promedio === null ? null : round((float) $promedio, 1),
                ];
            })
            ->sortByDesc('reservas')->values()->all();
    }
}
