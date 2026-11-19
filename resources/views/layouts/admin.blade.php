{{--
    Plantilla del panel del administrador: cabecera, menú lateral y contenido.
    Cada página define: @section('seccion', 'resumen'|'usuarios'|'destinos'|'excursiones'|'reservas'|'solicitudes'|'resenas'|'reporte') y @section('panel')
--}}
@extends('layouts.app')

@php
    $seccion = trim($__env->yieldContent('seccion', 'resumen'));
    $pendientesMenu = \App\Models\Reserva::where('estado', 'pendiente')->count();
    $solicitudesMenu = \App\Models\Solicitud::where('estado', 'pendiente')->count();
    $contadores = ['reservas' => $pendientesMenu, 'solicitudes' => $solicitudesMenu];
    $menu = [
        'resumen' => ['Resumen', 'bi-grid-1x2', route('admin.panel')],
        'usuarios' => ['Usuarios y agencias', 'bi-people', route('admin.usuarios.index')],
        'destinos' => ['Destinos', 'bi-geo-alt', route('admin.destinos.index')],
        'excursiones' => ['Excursiones', 'bi-compass', route('admin.excursiones.index')],
        'reservas' => ['Reservas', 'bi-calendar2-check', route('admin.reservas')],
        'solicitudes' => ['Asesorías y vehículos', 'bi-chat-heart', route('admin.solicitudes.index')],
        'resenas' => ['Reseñas', 'bi-star', route('admin.resenas.index')],
        'reporte' => ['Reporte (Python)', 'bi-bar-chart-line', route('admin.reporte')],
    ];
@endphp

@section('contenido')

<header class="panel-cabecera">
    <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="panel-icono"><i class="bi bi-shield-check"></i></span>
            <div>
                <h1 class="panel-titulo">Panel del administrador</h1>
                <p class="panel-subtitulo mb-0">Usuarios, agencias, destinos, contenido y reservas de Explora RD</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-pastilla-clara"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button>
        </form>
    </div>
</header>

<section class="seccion seccion-clara pt-5">
    <div class="container">
        <div class="row g-4">
            <aside class="col-lg-3">
                <nav class="menu-panel" aria-label="Menú del administrador">
                    @foreach ($menu as $clave => [$texto, $icono, $url])
                        <a href="{{ $url }}" class="{{ $seccion === $clave ? 'activo' : '' }}">
                            <i class="bi {{ $icono }}"></i> {{ $texto }}
                            @if (! empty($contadores[$clave]))
                                <small class="contador-menu">{{ $contadores[$clave] }}</small>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div class="col-lg-9">
                @yield('panel')
            </div>
        </div>
    </div>
</section>

@endsection
