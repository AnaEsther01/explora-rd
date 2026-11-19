@extends('layouts.admin')

@section('titulo', 'Reseñas — Panel del administrador | Explora RD')
@section('seccion', 'resenas')

@section('panel')
<div class="caja-panel">
    <h2 class="caja-lateral-titulo">Reseñas de los viajeros</h2>
    <p class="small text-muted">Solo pueden opinar los turistas que ya hicieron la excursión. Oculta las reseñas ofensivas o que no tengan que ver con la excursión; si la ocultas, deja de contar en el promedio de estrellas.</p>

    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-sm-4">
            <label for="estrellas" class="form-label">Estrellas</label>
            <select id="estrellas" name="estrellas" class="form-select" onchange="this.form.submit()">
                <option value="">Todas</option>
                @for ($e = 5; $e >= 1; $e--)
                    <option value="{{ $e }}" @selected($estrellas === $e)>{{ $e }} {{ $e === 1 ? 'estrella' : 'estrellas' }}</option>
                @endfor
            </select>
        </div>
        <div class="col-sm-4">
            <label for="estado" class="form-label">Estado</label>
            <select id="estado" name="estado" class="form-select" onchange="this.form.submit()">
                <option value="">Visibles y ocultas</option>
                <option value="visibles" @selected($estado === 'visibles')>Solo visibles</option>
                <option value="ocultas" @selected($estado === 'ocultas')>Solo ocultas</option>
            </select>
        </div>
        <div class="col-sm-4">
            <a href="{{ route('admin.resenas.index') }}" class="btn btn-contorno w-100">Quitar filtros</a>
        </div>
    </form>

    @forelse ($resenas as $resena)
        <div class="resena {{ $resena->visible ? '' : 'fila-oculta' }}">
            <div class="resena-cabecera">
                <span class="resena-avatar">{{ mb_strtoupper(mb_substr($resena->usuario->nombre, 0, 1)) }}</span>
                <div>
                    <strong>{{ $resena->usuario->nombre }} <small class="text-muted">· {{ $resena->usuario->correo }}</small></strong>
                    <small>
                        <a href="{{ route('excursiones.show', $resena->excursion) }}#resenas" target="_blank" rel="noopener">{{ $resena->excursion->titulo }}</a>
                        · {{ $resena->excursion->agencia->nombreVisible() }} · {{ $resena->created_at->format('d/m/Y') }}
                    </small>
                </div>
                @include('partials.estrellas', ['valor' => $resena->estrellas])
                <div class="d-flex align-items-center gap-1">
                    <form method="POST" action="{{ route('admin.resenas.visible', $resena) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="interruptor {{ $resena->visible ? 'encendido' : '' }}" title="{{ $resena->visible ? 'Ocultar' : 'Mostrar' }}">
                            <span></span> {{ $resena->visible ? 'Visible' : 'Oculta' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.resenas.destroy', $resena) }}"
                          onsubmit="return confirm('¿Borrar esta reseña? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icono btn-icono-peligro" title="Borrar"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
            @if ($resena->comentario)
                <p>{{ $resena->comentario }}</p>
            @endif
        </div>
    @empty
        <div class="aviso-vacio"><i class="bi bi-star"></i><p>No hay reseñas con esos filtros.</p></div>
    @endforelse

    <div class="mt-4 d-flex justify-content-center">{{ $resenas->links() }}</div>
</div>
@endsection
