@extends('layouts.admin')

@section('titulo', 'Excursiones — Panel del administrador | Explora RD')
@section('seccion', 'excursiones')

@section('panel')
<div class="caja-panel">
    <h2 class="caja-lateral-titulo">Excursiones de todas las agencias</h2>
    <p class="small text-muted">Cada agencia escribe sus excursiones. Aquí puedes ocultar o borrar las que no cumplan las normas de Explora RD.</p>

    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-md-3">
            <label for="buscar" class="form-label">Título</label>
            <input type="search" id="buscar" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Buscar...">
        </div>
        <div class="col-md-3">
            <label for="agencia" class="form-label">Agencia</label>
            <select id="agencia" name="agencia" class="form-select" onchange="this.form.submit()">
                <option value="">Todas</option>
                @foreach ($agencias as $a)
                    <option value="{{ $a->id }}" @selected($agenciaId === $a->id)>{{ $a->nombreVisible() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="destino" class="form-label">Destino</label>
            <select id="destino" name="destino" class="form-select" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach ($destinos as $d)
                    <option value="{{ $d->id }}" @selected($destinoId === $d->id)>{{ $d->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="estado" class="form-label">Estado</label>
            <select id="estado" name="estado" class="form-select" onchange="this.form.submit()">
                <option value="">Visibles y ocultas</option>
                <option value="visibles" @selected($estado === 'visibles')>Solo visibles</option>
                <option value="ocultas" @selected($estado === 'ocultas')>Solo ocultas</option>
            </select>
        </div>
    </form>

    @if ($excursiones->isEmpty())
        <div class="aviso-vacio"><i class="bi bi-compass"></i><p>No hay excursiones con esos filtros.</p></div>
    @else
        <div class="table-responsive">
            <table class="table tabla-panel align-middle mb-0">
                <thead><tr><th>Excursión</th><th>Agencia</th><th>Precio</th><th>Reservas</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                    @foreach ($excursiones as $excursion)
                        <tr class="{{ $excursion->disponible ? '' : 'fila-oculta' }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="miniatura">@include('partials.foto', ['ruta' => $excursion->foto, 'tipo' => $excursion->destino->tipo, 'alt' => ''])</div>
                                    <div>
                                        <strong class="d-block">{{ $excursion->titulo }}</strong>
                                        <small class="text-muted">{{ $excursion->nombreTipo() }} · {{ $excursion->destino->nombre }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ $excursion->agencia->nombreVisible() }}
                                @unless ($excursion->agencia->activo) <small class="d-block text-danger">Agencia suspendida</small> @endunless
                            </td>
                            <td class="text-nowrap">{{ $excursion->precioFormateado() }}</td>
                            <td>{{ $excursion->reservas_count }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.excursiones.disponible', $excursion) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="interruptor {{ $excursion->disponible ? 'encendido' : '' }}" title="{{ $excursion->disponible ? 'Ocultar' : 'Mostrar' }}">
                                        <span></span> {{ $excursion->disponible ? 'Visible' : 'Oculta' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end text-nowrap">
                                @if ($excursion->esPublica())
                                    <a href="{{ route('excursiones.show', $excursion) }}" class="btn-icono" target="_blank" rel="noopener" title="Ver ficha"><i class="bi bi-eye"></i></a>
                                @endif
                                <form method="POST" action="{{ route('admin.excursiones.destroy', $excursion) }}" class="d-inline"
                                      onsubmit="return confirm('¿Borrar «{{ addslashes($excursion->titulo) }}»? Esta acción no se puede deshacer.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icono btn-icono-peligro" title="Borrar"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-center">{{ $excursiones->links() }}</div>
    @endif
</div>
@endsection
