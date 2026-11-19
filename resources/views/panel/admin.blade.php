@extends('layouts.admin')

@section('titulo', 'Panel del administrador — Explora RD')
@section('seccion', 'resumen')

@section('panel')

<div class="row g-3 mb-4">
    @foreach ([
        ['Turistas', $totales['turistas'], 'bi-person-walking', route('admin.usuarios.index', ['rol' => 'turista'])],
        ['Agencias', $totales['agencias'], 'bi-building', route('admin.usuarios.index', ['rol' => 'agencia'])],
        ['Destinos', $totales['destinos'], 'bi-geo-alt', route('admin.destinos.index')],
        ['Excursiones', $totales['excursiones'], 'bi-compass', route('admin.excursiones.index')],
        ['Reservas', $totales['reservas'], 'bi-calendar2-check', route('admin.reservas')],
        ['Pendientes', $totales['pendientes'], 'bi-hourglass-split', route('admin.reservas', ['estado' => 'pendiente'])],
    ] as [$texto, $numero, $icono, $url])
        <div class="col-6 col-md-4">
            <a href="{{ $url }}" class="tarjeta-total tarjeta-total-enlace"><i class="bi {{ $icono }}"></i><span>{{ $texto }}</span><strong>{{ $numero }}</strong></a>
        </div>
    @endforeach
</div>

@if ($totales['solicitudes'])
    <div class="caja-consejos mb-4">
        <i class="bi bi-chat-heart"></i>
        <div><strong>{{ $totales['solicitudes'] }} {{ $totales['solicitudes'] === 1 ? 'solicitud pendiente' : 'solicitudes pendientes' }}</strong> de asesoría o renta de vehículos. Los turistas esperan que los contactes.</div>
        <a href="{{ route('admin.solicitudes.index', ['estado' => 'pendiente']) }}" class="btn btn-contorno btn-sm ms-auto align-self-center">Atender</a>
    </div>
@endif

@if ($totales['suspendidos'])
    <div class="caja-consejos mb-4">
        <i class="bi bi-person-lock"></i>
        <div><strong>{{ $totales['suspendidos'] }} {{ $totales['suspendidos'] === 1 ? 'cuenta suspendida' : 'cuentas suspendidas' }}.</strong> No pueden entrar y, si son agencias, sus excursiones no se muestran.</div>
        <a href="{{ route('admin.usuarios.index', ['estado' => 'suspendidos']) }}" class="btn btn-contorno btn-sm ms-auto align-self-center">Ver</a>
    </div>
@endif

<div class="caja-panel">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="caja-lateral-titulo mb-0">Usuarios recientes</h2>
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-turquesa"><i class="bi bi-person-plus"></i> Nuevo usuario</a>
    </div>
    <div class="table-responsive">
        <table class="table tabla-panel align-middle mb-0">
            <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Registro</th><th></th></tr></thead>
            <tbody>
                @foreach ($usuariosRecientes as $u)
                    <tr class="{{ $u->activo ? '' : 'fila-oculta' }}">
                        <td>{{ $u->nombreVisible() }}</td>
                        <td class="text-break">{{ $u->correo }}</td>
                        <td><span class="etiqueta-rol rol-{{ $u->rol }}">{{ ucfirst($u->rol) }}</span></td>
                        <td class="text-nowrap">{{ $u->created_at?->format('d/m/Y') }}</td>
                        <td class="text-end"><a href="{{ route('admin.usuarios.edit', $u) }}" class="btn-icono" title="Editar"><i class="bi bi-pencil"></i></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
