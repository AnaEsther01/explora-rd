@extends('layouts.agencia')

@section('titulo', 'Reservas — Panel de la agencia | Explora RD')
@section('seccion', 'reservas')

@php
    $pestanas = [
        null => ['Todas', $conteo->sum()],
        'pendiente' => ['Pendientes', $conteo['pendiente'] ?? 0],
        'confirmada' => ['Confirmadas', $conteo['confirmada'] ?? 0],
        'cancelada' => ['Canceladas', $conteo['cancelada'] ?? 0],
    ];
@endphp

@section('panel')
<div class="caja-panel">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h2 class="caja-lateral-titulo mb-0">Reservas</h2>
        <form method="GET" class="buscador-panel" role="search">
            @if ($estado) <input type="hidden" name="estado" value="{{ $estado }}"> @endif
            <i class="bi bi-search"></i>
            <input type="search" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Buscar cliente o excursión" aria-label="Buscar reserva">
        </form>
    </div>

    {{-- Pestañas por estado --}}
    <div class="filtros-pastilla mb-4" role="group" aria-label="Filtrar por estado">
        @foreach ($pestanas as $clave => [$texto, $numero])
            <a href="{{ route('agencia.reservas.index', array_filter(['estado' => $clave, 'buscar' => $buscar])) }}"
               class="pastilla-filtro {{ $estado === ($clave ?: null) ? 'activa' : '' }}">
                {{ $texto }} <span class="contador-pastilla">{{ $numero }}</span>
            </a>
        @endforeach
    </div>

    @if ($reservas->isEmpty())
        <div class="aviso-vacio">
            <i class="bi bi-calendar"></i>
            <p>
                @if ($buscar !== '') No hay reservas que coincidan con «{{ $buscar }}».
                @elseif ($estado) No tienes reservas {{ $estado === 'pendiente' ? 'pendientes' : ($estado === 'confirmada' ? 'confirmadas' : 'canceladas') }}.
                @else Todavía no has recibido reservas. Cuando un turista solicite una, aparecerá aquí.
                @endif
            </p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table tabla-panel align-middle mb-0">
                <thead>
                    <tr><th>Cliente</th><th>Excursión</th><th>Fecha</th><th>Personas</th><th>Total</th><th>Estado</th><th class="text-end">Acciones</th></tr>
                </thead>
                <tbody>
                    @foreach ($reservas as $reserva)
                        <tr class="{{ $reserva->estaPendiente() ? 'fila-pendiente' : '' }}">
                            <td>
                                <strong class="d-block">{{ $reserva->usuario->nombre }}</strong>
                                <small class="text-muted">Solicitada el {{ $reserva->created_at?->format('d/m/Y') }}</small>
                            </td>
                            <td>{{ $reserva->excursion->titulo }}</td>
                            <td class="text-nowrap">{{ $reserva->fecha->format('d/m/Y') }}</td>
                            <td>{{ $reserva->cantidad_personas }}</td>
                            <td class="text-nowrap">{{ $reserva->totalFormateado() }}</td>
                            <td>@include('panel._estado', ['estado' => $reserva->estado])</td>
                            <td>@include('agencia.reservas._acciones', ['reserva' => $reserva])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-center">{{ $reservas->links() }}</div>
    @endif
</div>
@endsection
