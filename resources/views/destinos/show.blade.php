@extends('layouts.app')

@php
    $tipoInfo = \App\Models\Destino::TIPOS[$destino->tipo] ?? ['nombre' => ucfirst($destino->tipo), 'icono' => 'bi-geo-alt'];
    // Descripción y actividades en el idioma elegido (si están traducidas)
    $descripcion = $destino->descripcionTraducida();
    $actividades = $destino->actividadesTraducidas();
    // Fotos de la galería del destino: la del destino + las de sus excursiones que ya tengan foto
    $fotos = collect([$destino->foto])
        ->merge($excursiones->pluck('foto'))
        ->filter(fn ($f) => $f && file_exists(public_path($f)))
        ->unique()->values();
@endphp

@section('titulo', $destino->nombre.' — '.__('Destinos de República Dominicana').' | Explora RD')
@section('descripcion', $destino->resumen().' '.__('Actividades y excursiones en :lugar.', ['lugar' => $destino->nombre.', '.$destino->provincia]))

@section('contenido')

@include('partials.cabecera', [
    'foto' => $destino->foto,
    'alta' => true,
    'etiqueta' => '<i class="bi '.$tipoInfo['icono'].'"></i> '.e(__($tipoInfo['nombre'])).' · '.e($destino->provincia),
    'titulo' => $destino->nombre,
    'subtitulo' => $destino->resumen(),
    'migas' => [__('Inicio') => route('inicio'), __('Destinos') => route('destinos.index'), $destino->nombre => null],
])

<section class="seccion">
    <div class="container">
        <div class="row g-4 g-lg-5">
            {{-- Columna principal: descripción y actividades --}}
            <div class="col-lg-8">
                <span class="seccion-etiqueta">{{ __('Sobre el destino') }}</span>
                <h2 class="seccion-titulo">{{ __('Conoce :lugar', ['lugar' => $destino->nombre]) }}</h2>
                <p class="texto-largo">{{ $descripcion }}</p>

                @if (! empty($actividades))
                    <h3 class="subtitulo-lora mt-5 mb-3">{{ __('Qué hacer en :lugar', ['lugar' => $destino->nombre]) }}</h3>
                    <ul class="lista-actividades">
                        @foreach ($actividades as $actividad)
                            <li><i class="bi bi-check2-circle"></i> {{ $actividad }}</li>
                        @endforeach
                    </ul>
                @endif

                @if ($fotos->count() > 1)
                    <h3 class="subtitulo-lora mt-5 mb-3">{{ __('Fotos') }}</h3>
                    <div class="fotos-destino">
                        @foreach ($fotos as $foto)
                            <img src="{{ asset($foto) }}" alt="{{ __('Foto de :lugar', ['lugar' => $destino->nombre]) }}" loading="lazy">
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Columna lateral: datos rápidos --}}
            <aside class="col-lg-4">
                <div class="caja-lateral">
                    <h3 class="caja-lateral-titulo">{{ __('Datos del destino') }}</h3>
                    <ul class="datos-lista">
                        <li><i class="bi bi-geo-alt"></i> <span>{{ __('Provincia') }}</span> <strong>{{ $destino->provincia }}</strong></li>
                        <li><i class="bi {{ $tipoInfo['icono'] }}"></i> <span>{{ __('Tipo') }}</span> <strong>{{ __($tipoInfo['nombre']) }}</strong></li>
                        <li><i class="bi bi-compass"></i> <span>{{ __('Experiencias') }}</span> <strong>{{ $excursiones->count() }}</strong></li>
                        @if ($excursiones->isNotEmpty())
                            <li><i class="bi bi-tag"></i> <span>{{ __('Desde') }}</span> <strong>{{ $excursiones->first()->precioFormateado() }}</strong></li>
                        @endif
                    </ul>
                    <a href="{{ route('excursiones.index', ['destino' => $destino->nombre]) }}" class="btn btn-turquesa w-100 mb-2">
                        <i class="bi bi-search"></i> {{ __('Buscar experiencias aquí') }}
                    </a>
                    <a href="{{ route('servicios.asesorias', ['lugar' => $destino->nombre]) }}#solicitar" class="btn btn-contorno w-100">
                        <i class="bi bi-chat-heart"></i> {{ __('Pedir asesoría') }}
                    </a>

                    {{-- Ubicación en el mapa --}}
                    @if ($destino->tieneUbicacion())
                        <h3 class="caja-lateral-titulo mt-4 mb-2">{{ __('Ubicación') }}</h3>
                        @include('partials.mapa', ['destinos' => collect([$destino]), 'alto' => 'mapa-pequeno', 'zoom' => 9])
                        <a href="{{ $destino->enlaceComoLlegar() }}" class="enlace-flecha d-inline-block mt-2" target="_blank" rel="noopener">
                            <i class="bi bi-sign-turn-right"></i> {{ __('Cómo llegar') }}
                        </a>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</section>

{{-- Excursiones disponibles en este destino --}}
<section class="seccion seccion-clara">
    <div class="container">
        <div class="seccion-encabezado">
            <div>
                <span class="seccion-etiqueta">{{ __('Experiencias') }}</span>
                <h2 class="seccion-titulo">{{ __('Excursiones en :lugar', ['lugar' => $destino->nombre]) }}</h2>
                <p class="seccion-texto">{{ __('Organizadas por agencias locales. Contacta directamente por WhatsApp.') }}</p>
            </div>
        </div>

        <div class="row g-4">
            @forelse ($excursiones as $excursion)
                <div class="col-12 col-md-6 col-lg-4">
                    @include('partials.tarjeta-excursion', ['excursion' => $excursion])
                </div>
            @empty
                <div class="col-12">
                    <div class="aviso-vacio">
                        <i class="bi bi-compass"></i>
                        <p>{{ __('Todavía no hay excursiones publicadas en :lugar.', ['lugar' => $destino->nombre]) }}<br>
                           {{ __('¿Quieres que te ayudemos a organizar tu visita?') }}</p>
                        <a href="https://wa.me/{{ config('explora.whatsapp') }}?text={{ rawurlencode(__('¡Hola! Quisiera información para visitar :lugar.', ['lugar' => $destino->nombre])) }}"
                           class="btn btn-turquesa" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> {{ __('Escríbenos') }}</a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- Otros destinos --}}
<section class="seccion">
    <div class="container">
        <div class="seccion-encabezado">
            <div>
                <span class="seccion-etiqueta">{{ __('Sigue explorando') }}</span>
                <h2 class="seccion-titulo">{{ __('Otros destinos') }}</h2>
            </div>
            <a href="{{ route('destinos.index') }}" class="enlace-ver-todo">{{ __('Ver todos los destinos') }} <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            @foreach ($otros as $otro)
                <div class="col-12 col-sm-6 col-lg-3">
                    @include('partials.tarjeta-destino', ['destino' => $otro])
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
