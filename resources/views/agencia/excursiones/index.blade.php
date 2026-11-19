@extends('layouts.agencia')

@section('titulo', ($verPaquetes ? 'Paquetes' : 'Mis excursiones').' — Panel de la agencia | Explora RD')
@section('seccion', $verPaquetes ? 'paquetes' : 'excursiones')

@section('panel')
<div class="caja-panel">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="caja-lateral-titulo mb-1">{{ $verPaquetes ? 'Circuitos y paquetes' : 'Mis excursiones' }}</h2>
            <p class="small text-muted mb-0">
                {{ $verPaquetes ? 'Rutas de varios lugares o días en un mismo viaje.' : 'Experiencias de un día o de unas horas.' }}
            </p>
        </div>
        <a href="{{ route('agencia.excursiones.create', $verPaquetes ? ['tipo' => 'paquete'] : []) }}" class="btn btn-turquesa">
            <i class="bi bi-plus-lg"></i> {{ $verPaquetes ? 'Nuevo paquete' : 'Nueva excursión' }}
        </a>
    </div>

    <form method="GET" class="buscador-panel mb-3" role="search">
        @if ($verPaquetes) <input type="hidden" name="ver" value="paquetes"> @endif
        <i class="bi bi-search"></i>
        <input type="search" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Buscar por título..." aria-label="Buscar por título">
    </form>

    @if ($excursiones->isEmpty())
        <div class="aviso-vacio">
            <i class="bi {{ $verPaquetes ? 'bi-box-seam' : 'bi-compass' }}"></i>
            @if ($buscar !== '')
                <p>No hay resultados para «{{ $buscar }}».</p>
            @else
                <p>Todavía no has publicado {{ $verPaquetes ? 'circuitos ni paquetes' : 'excursiones' }}.<br>¡Publica la primera para que los turistas te encuentren!</p>
                <a href="{{ route('agencia.excursiones.create', $verPaquetes ? ['tipo' => 'paquete'] : []) }}" class="btn btn-turquesa">Publicar ahora</a>
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table tabla-panel align-middle mb-0">
                <thead>
                    <tr>
                        <th>Excursión</th>
                        <th>Destino</th>
                        <th>Precio</th>
                        <th>Reservas</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($excursiones as $excursion)
                        <tr class="{{ $excursion->disponible ? '' : 'fila-oculta' }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="miniatura">
                                        @include('partials.foto', ['ruta' => $excursion->foto, 'tipo' => $excursion->destino->tipo, 'alt' => ''])
                                    </div>
                                    <div>
                                        <strong class="d-block">{{ $excursion->titulo }}</strong>
                                        <small class="text-muted">{{ $excursion->nombreTipo() }}@if ($excursion->nombreCategoria()) · {{ $excursion->nombreCategoria() }}@endif · {{ $excursion->duracion ?: '—' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $excursion->destino->nombre }}</td>
                            <td class="text-nowrap">{{ $excursion->precioFormateado() }}</td>
                            <td>{{ $excursion->reservas_count }}</td>
                            <td>
                                <form method="POST" action="{{ route('agencia.excursiones.disponible', $excursion) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="interruptor {{ $excursion->disponible ? 'encendido' : '' }}"
                                            title="{{ $excursion->disponible ? 'Ocultar' : 'Publicar' }}">
                                        <span></span> {{ $excursion->disponible ? 'Publicada' : 'Oculta' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end text-nowrap">
                                @if ($excursion->disponible)
                                    <a href="{{ route('excursiones.show', $excursion) }}" class="btn-icono" target="_blank" rel="noopener" title="Ver como turista"><i class="bi bi-eye"></i></a>
                                @endif
                                <a href="{{ route('agencia.excursiones.edit', $excursion) }}" class="btn-icono" title="Editar"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('agencia.excursiones.destroy', $excursion) }}" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar «{{ addslashes($excursion->titulo) }}»? Esta acción no se puede deshacer.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icono btn-icono-peligro" title="Eliminar"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
