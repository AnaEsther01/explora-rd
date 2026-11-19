@extends('layouts.admin')

@section('titulo', 'Destinos — Panel del administrador | Explora RD')
@section('seccion', 'destinos')

@section('panel')
<div class="caja-panel">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h2 class="caja-lateral-titulo mb-0">Destinos ({{ $destinos->count() }})</h2>
        <div class="d-flex gap-2 flex-wrap">
            <form method="GET" class="buscador-panel" role="search">
                <i class="bi bi-search"></i>
                <input type="search" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Destino o provincia" aria-label="Buscar destino">
            </form>
            <a href="{{ route('admin.destinos.create') }}" class="btn btn-turquesa"><i class="bi bi-plus-lg"></i> Nuevo destino</a>
        </div>
    </div>

    @if ($destinos->isEmpty())
        <div class="aviso-vacio"><i class="bi bi-geo-alt"></i><p>No hay destinos{{ $buscar !== '' ? ' que coincidan con «'.$buscar.'»' : '' }}.</p></div>
    @else
        <div class="table-responsive">
            <table class="table tabla-panel align-middle mb-0">
                <thead><tr><th>Destino</th><th>Provincia</th><th>Tipo</th><th>Actividades</th><th>Excursiones</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                    @foreach ($destinos as $destino)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="miniatura">@include('partials.foto', ['ruta' => $destino->foto, 'tipo' => $destino->tipo, 'alt' => ''])</div>
                                    <strong>{{ $destino->nombre }}</strong>
                                </div>
                            </td>
                            <td>{{ $destino->provincia }}</td>
                            <td>{{ \App\Models\Destino::TIPOS[$destino->tipo]['nombre'] ?? $destino->tipo }}</td>
                            <td>{{ count($destino->actividades ?? []) }}</td>
                            <td>{{ $destino->excursiones_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('destinos.show', $destino) }}" class="btn-icono" target="_blank" rel="noopener" title="Ver página"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.destinos.edit', $destino) }}" class="btn-icono" title="Editar"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.destinos.destroy', $destino) }}" class="d-inline"
                                      onsubmit="return confirm('¿Borrar el destino {{ addslashes($destino->nombre) }}?')">
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
    @endif
</div>
@endsection
