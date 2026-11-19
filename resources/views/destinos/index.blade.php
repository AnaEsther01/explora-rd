@extends('layouts.app')

@section('titulo', __('Destinos turísticos de República Dominicana').' — Explora RD')
@section('descripcion', __('Descubre las provincias y destinos de República Dominicana: playas, montañas y ciudades llenas de historia.'))

@section('contenido')

@include('partials.cabecera', [
    'foto' => 'img/destinos/punta-cana.jpg',
    'etiqueta' => e(__('Destinos')),
    'titulo' => __('Descubre República Dominicana'),
    'subtitulo' => __('Playas de arena blanca, montañas con cascadas y ciudades llenas de historia. Elige tu próximo destino.'),
    'migas' => [__('Inicio') => route('inicio'), __('Destinos') => null],
])

<section class="seccion seccion-clara pt-5">
    <div class="container">
        {{-- Filtro por tipo de destino --}}
        <div class="filtros-pastilla mb-4" role="group" aria-label="{{ __('Filtrar destinos por tipo') }}">
            <a href="{{ route('destinos.index') }}" class="pastilla-filtro {{ $tipoActual ? '' : 'activa' }}">
                <i class="bi bi-grid"></i> {{ __('Todos') }}
            </a>
            @foreach (\App\Models\Destino::TIPOS as $clave => $tipo)
                <a href="{{ route('destinos.index', ['tipo' => $clave]) }}" class="pastilla-filtro {{ $tipoActual === $clave ? 'activa' : '' }}">
                    <i class="bi {{ $tipo['icono'] }}"></i> {{ __($tipo['nombre']) }}
                </a>
            @endforeach
        </div>

        {{-- Mapa con todos los destinos (o los del tipo elegido) --}}
        @if ($destinos->filter->tieneUbicacion()->isNotEmpty())
            <div class="caja-mapa mb-4">
                <div class="caja-mapa-titulo">
                    <h2 class="subtitulo-lora mb-0"><i class="bi bi-map"></i> {{ __('Explora en el mapa') }}</h2>
                    <small>{{ __('Toca un punto para ver el destino') }}</small>
                </div>
                @include('partials.mapa', ['destinos' => $destinos])
                <div class="leyenda-mapa">
                    @foreach (\App\Models\Destino::TIPOS as $clave => $tipo)
                        <span><i class="punto-leyenda marcador-{{ $clave === 'montaña' ? 'montana' : $clave }}"></i> {{ __($tipo['nombre']) }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <p class="texto-resultados">
            {{ trans_choice(':count destino|:count destinos', $destinos->count()) }}
            @if ($tipoActual) · <strong>{{ __(\App\Models\Destino::TIPOS[$tipoActual]['nombre']) }}</strong> @endif
        </p>

        <div class="row g-4">
            @forelse ($destinos as $destino)
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    @include('partials.tarjeta-destino', ['destino' => $destino])
                </div>
            @empty
                <div class="col-12">
                    <div class="aviso-vacio">
                        <i class="bi bi-map"></i>
                        <p>{{ __('No hay destinos de este tipo todavía.') }}</p>
                        <a href="{{ route('destinos.index') }}" class="btn btn-turquesa">{{ __('Ver todos los destinos') }}</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

@endsection
