@extends('layouts.agencia')

@section('titulo', 'Panel de la agencia — Explora RD')
@section('seccion', 'resumen')

@section('panel')

{{-- Tarjetas con totales (boceto 19) --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="tarjeta-total"><i class="bi bi-calendar2-check"></i><span>Total de reservas</span><strong>{{ $totales['reservas'] }}</strong></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="tarjeta-total total-alerta"><i class="bi bi-hourglass-split"></i><span>Reservas pendientes</span><strong>{{ $totales['pendientes'] }}</strong></div>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('agencia.excursiones.index') }}" class="tarjeta-total tarjeta-total-enlace"><i class="bi bi-compass"></i><span>Excursiones publicadas</span><strong>{{ $totales['excursiones'] }}</strong></a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('agencias.show', $agencia) }}" target="_blank" rel="noopener" class="tarjeta-total tarjeta-total-enlace"><i class="bi bi-eye"></i><span>Visitas al perfil</span><strong>{{ $totales['visitas'] }}</strong></a>
    </div>
</div>

{{-- Lo que opinan los viajeros de esta agencia --}}
<div class="franja-agencia mb-4">
    <span><i class="bi bi-heart-fill"></i> Tus excursiones fueron guardadas en favoritos <strong>{{ $totales['favoritos'] }} {{ $totales['favoritos'] === 1 ? 'vez' : 'veces' }}</strong></span>
    <span>
        <i class="bi bi-star-fill"></i>
        @if ($totales['resenas'])
            Calificación promedio <strong>{{ number_format($totales['estrellas'], 1) }}</strong> de 5 ({{ $totales['resenas'] }} {{ $totales['resenas'] === 1 ? 'reseña' : 'reseñas' }})
        @else
            Todavía no tienes reseñas
        @endif
    </span>
</div>

{{-- Consejos si el perfil está incompleto --}}
@if ($pendientesPerfil)
    <div class="caja-consejos mb-4">
        <i class="bi bi-lightbulb"></i>
        <div>
            <strong>Completa tu perfil para atraer más turistas:</strong>
            <ul class="mb-0">
                @foreach ($pendientesPerfil as $consejo)
                    <li>{{ $consejo }}</li>
                @endforeach
            </ul>
        </div>
        <a href="{{ route('agencia.perfil') }}" class="btn btn-contorno btn-sm ms-auto align-self-center">Completar</a>
    </div>
@endif

<div class="caja-panel">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h2 class="caja-lateral-titulo mb-0">Reservas recientes</h2>
        <div class="d-flex gap-2 flex-wrap">
            <form method="GET" class="buscador-panel" role="search">
                <i class="bi bi-search"></i>
                <input type="search" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Buscar reserva" aria-label="Buscar reserva">
            </form>
            <a href="{{ route('agencia.excursiones.create') }}" class="btn btn-turquesa"><i class="bi bi-plus-lg"></i> Nueva excursión</a>
        </div>
    </div>

    @if ($recientes->isEmpty())
        <div class="aviso-vacio">
            <i class="bi bi-calendar"></i>
            <p>{{ $buscar !== '' ? 'No hay reservas que coincidan con «'.$buscar.'».' : 'Todavía no has recibido reservas.' }}</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table tabla-panel align-middle mb-0">
                <thead>
                    <tr><th>Cliente</th><th>Excursión</th><th>Fecha</th><th>Personas</th><th>Estado</th><th class="text-end">Acciones</th></tr>
                </thead>
                <tbody>
                    @foreach ($recientes as $reserva)
                        <tr class="{{ $reserva->estaPendiente() ? 'fila-pendiente' : '' }}">
                            <td>{{ $reserva->usuario->nombre }}</td>
                            <td>{{ $reserva->excursion->titulo }}</td>
                            <td class="text-nowrap">{{ $reserva->fecha->format('d/m/Y') }}</td>
                            <td>{{ $reserva->cantidad_personas }}</td>
                            <td>@include('panel._estado', ['estado' => $reserva->estado])</td>
                            <td>@include('agencia.reservas._acciones', ['reserva' => $reserva])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-end mt-3">
            <a href="{{ route('agencia.reservas.index') }}" class="enlace-ver-todo">Ver todas las reservas <i class="bi bi-arrow-right"></i></a>
        </div>
    @endif
</div>
@endsection
