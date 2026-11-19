{{--
    Plantilla del panel privado de la agencia (boceto 19):
    cabecera con el nombre, menú lateral y el contenido de cada sección.
    Cada página define: @section('seccion', 'resumen'|'perfil'|'excursiones'|'paquetes') y @section('panel')
--}}
@extends('layouts.app')

@php
    $agencia = auth()->user();
    $seccion = trim($__env->yieldContent('seccion', 'resumen'));
    // Reservas pendientes de esta agencia (para la campanita y el menú)
    $pendientesMenu = \App\Models\Reserva::where('estado', 'pendiente')
        ->whereHas('excursion', fn ($q) => $q->where('agencia_id', $agencia->id))
        ->count();
@endphp

@section('contenido')

<header class="panel-cabecera">
    <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            @if ($agencia->tieneLogo())
                <img src="{{ asset($agencia->logo) }}" alt="Logo de {{ $agencia->nombreVisible() }}" class="panel-logo">
            @else
                <span class="panel-icono">{{ $agencia->iniciales() }}</span>
            @endif
            <div>
                <h1 class="panel-titulo">Panel de la agencia: «{{ $agencia->nombreVisible() }}»</h1>
                <p class="panel-subtitulo mb-0">Administra tu perfil, tus excursiones y tus paquetes</p>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            {{-- Campanita: reservas pendientes por responder --}}
            <a href="{{ route('agencia.reservas.index', ['estado' => 'pendiente']) }}" class="btn btn-pastilla-clara campanita"
               title="{{ $pendientesMenu }} {{ $pendientesMenu === 1 ? 'reserva pendiente' : 'reservas pendientes' }}">
                <i class="bi bi-bell"></i>
                @if ($pendientesMenu) <span class="campanita-numero">{{ $pendientesMenu }}</span> @endif
            </a>
            <a href="{{ route('agencias.show', $agencia) }}" class="btn btn-pastilla-clara" target="_blank" rel="noopener">
                <i class="bi bi-eye"></i> Ver mi perfil público
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-pastilla-clara"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button>
            </form>
        </div>
    </div>
</header>

<section class="seccion seccion-clara pt-5">
    <div class="container">
        <div class="row g-4">
            <aside class="col-lg-3">
                <nav class="menu-panel" aria-label="Menú del panel">
                    <a href="{{ route('agencia.panel') }}" class="{{ $seccion === 'resumen' ? 'activo' : '' }}">
                        <i class="bi bi-grid-1x2"></i> Resumen
                    </a>
                    <a href="{{ route('agencia.perfil') }}" class="{{ $seccion === 'perfil' ? 'activo' : '' }}">
                        <i class="bi bi-person-badge"></i> Mi perfil
                    </a>
                    <a href="{{ route('agencia.excursiones.index') }}" class="{{ $seccion === 'excursiones' ? 'activo' : '' }}">
                        <i class="bi bi-compass"></i> Mis excursiones
                    </a>
                    <a href="{{ route('agencia.excursiones.index', ['ver' => 'paquetes']) }}" class="{{ $seccion === 'paquetes' ? 'activo' : '' }}">
                        <i class="bi bi-box-seam"></i> Paquetes
                    </a>
                    <a href="{{ route('agencia.reservas.index') }}" class="{{ $seccion === 'reservas' ? 'activo' : '' }}">
                        <i class="bi bi-calendar-check"></i> Reservas
                        @if ($pendientesMenu) <small class="contador-menu">{{ $pendientesMenu }}</small> @endif
                    </a>
                </nav>
                <a href="{{ route('agencia.excursiones.create') }}" class="btn btn-turquesa w-100 mt-3">
                    <i class="bi bi-plus-lg"></i> Nueva excursión
                </a>
            </aside>

            <div class="col-lg-9">
                @yield('panel')
            </div>
        </div>
    </div>
</section>

@endsection
