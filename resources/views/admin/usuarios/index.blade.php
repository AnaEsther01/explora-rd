@extends('layouts.admin')

@section('titulo', 'Usuarios y agencias — Panel del administrador | Explora RD')
@section('seccion', 'usuarios')

@php
    $pestanas = [
        '' => ['Todos', $conteo->sum()],
        'turista' => ['Turistas', $conteo['turista'] ?? 0],
        'agencia' => ['Agencias', $conteo['agencia'] ?? 0],
        'administrador' => ['Administradores', $conteo['administrador'] ?? 0],
    ];
@endphp

@section('panel')
<div class="caja-panel">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h2 class="caja-lateral-titulo mb-0">Usuarios y agencias</h2>
        <div class="d-flex gap-2 flex-wrap">
            <form method="GET" class="buscador-panel" role="search">
                @if ($rol) <input type="hidden" name="rol" value="{{ $rol }}"> @endif
                @if ($estado) <input type="hidden" name="estado" value="{{ $estado }}"> @endif
                <i class="bi bi-search"></i>
                <input type="search" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Nombre, agencia o correo" aria-label="Buscar usuario">
            </form>
            <a href="{{ route('admin.usuarios.create', $rol ? ['rol' => $rol] : []) }}" class="btn btn-turquesa"><i class="bi bi-person-plus"></i> Nuevo</a>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
        <div class="filtros-pastilla" role="group" aria-label="Filtrar por rol">
            @foreach ($pestanas as $clave => [$texto, $numero])
                <a href="{{ route('admin.usuarios.index', array_filter(['rol' => $clave, 'estado' => $estado, 'buscar' => $buscar])) }}"
                   class="pastilla-filtro {{ ($rol ?? '') === $clave ? 'activa' : '' }}">
                    {{ $texto }} <span class="contador-pastilla">{{ $numero }}</span>
                </a>
            @endforeach
        </div>
        <form method="GET">
            @if ($rol) <input type="hidden" name="rol" value="{{ $rol }}"> @endif
            @if ($buscar) <input type="hidden" name="buscar" value="{{ $buscar }}"> @endif
            <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Filtrar por estado">
                <option value="">Activos y suspendidos</option>
                <option value="activos" @selected($estado === 'activos')>Solo activos</option>
                <option value="suspendidos" @selected($estado === 'suspendidos')>Solo suspendidos</option>
            </select>
        </form>
    </div>

    @if ($usuarios->isEmpty())
        <div class="aviso-vacio"><i class="bi bi-people"></i><p>No hay usuarios con esos filtros.</p></div>
    @else
        <div class="table-responsive">
            <table class="table tabla-panel align-middle mb-0">
                <thead>
                    <tr><th>Usuario</th><th>Rol</th><th>Actividad</th><th>Estado</th><th class="text-end">Acciones</th></tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $u)
                        @php $soyYo = $u->id === auth()->id(); @endphp
                        <tr class="{{ $u->activo ? '' : 'fila-oculta' }}">
                            <td>
                                <strong class="d-block">{{ $u->nombreVisible() }} @if ($soyYo) <small class="text-muted">(tú)</small> @endif</strong>
                                <small class="text-muted text-break">{{ $u->correo }}</small>
                            </td>
                            <td><span class="etiqueta-rol rol-{{ $u->rol }}">{{ ucfirst($u->rol) }}</span></td>
                            <td class="small text-muted text-nowrap">
                                @if ($u->esAgencia())
                                    {{ $u->excursiones_count }} excursiones
                                @elseif ($u->esTurista())
                                    {{ $u->reservas_count }} reservas
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($soyYo)
                                    <span class="estado estado-confirmada"><i class="bi bi-check-circle"></i> Activa</span>
                                @else
                                    <form method="POST" action="{{ route('admin.usuarios.activo', $u) }}"
                                          onsubmit="return confirm('{{ $u->activo ? '¿Suspender' : '¿Reactivar' }} la cuenta de {{ addslashes($u->nombreVisible()) }}?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="interruptor {{ $u->activo ? 'encendido' : '' }}" title="{{ $u->activo ? 'Suspender' : 'Reactivar' }}">
                                            <span></span> {{ $u->activo ? 'Activa' : 'Suspendida' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                @if ($u->esAgencia() && $u->activo)
                                    <a href="{{ route('agencias.show', $u) }}" class="btn-icono" target="_blank" rel="noopener" title="Ver perfil público"><i class="bi bi-eye"></i></a>
                                @endif
                                <a href="{{ route('admin.usuarios.edit', $u) }}" class="btn-icono" title="Editar"><i class="bi bi-pencil"></i></a>
                                @unless ($soyYo)
                                    <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}" class="d-inline"
                                          onsubmit="return confirm('¿Borrar la cuenta de {{ addslashes($u->nombreVisible()) }}? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icono btn-icono-peligro" title="Borrar"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-center">{{ $usuarios->links() }}</div>
    @endif
</div>
@endsection
