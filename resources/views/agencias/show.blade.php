@extends('layouts.app')

@php
    $nombre = $agencia->nombreVisible();
    $fotos = $agencia->fotosExistentes();
    $videos = $agencia->videosParaMostrar();
@endphp

@section('titulo', $nombre.' — '.__('Agencia turística').' | Explora RD')
@section('descripcion', \Illuminate\Support\Str::limit($agencia->descripcion ?: __('Agencia turística en República Dominicana.'), 155))

@section('contenido')

@include('partials.cabecera', [
    'foto' => $fotos[0] ?? null,
    'etiqueta' => '<i class="bi bi-patch-check"></i> '.e(__('Agencia turística local')),
    'titulo' => $nombre,
    'subtitulo' => $agencia->direccion,
    'migas' => [__('Inicio') => route('inicio'), __('Agencias') => route('agencias.index'), $nombre => null],
])

<section class="seccion">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-3 mb-4">
                    @if ($agencia->tieneLogo())
                        <img src="{{ asset($agencia->logo) }}" alt="{{ __('Logo de :nombre', ['nombre' => $nombre]) }}" class="perfil-logo">
                    @else
                        <span class="perfil-logo agencia-iniciales">{{ $agencia->iniciales() }}</span>
                    @endif
                    <div>
                        <span class="seccion-etiqueta mb-0">{{ __('Quiénes somos') }}</span>
                        <h2 class="seccion-titulo mb-0">{{ $nombre }}</h2>
                    </div>
                </div>

                <p class="texto-largo">{{ $agencia->descripcion ?: __('Esta agencia todavía no ha escrito su presentación.') }}</p>

                @if ($fotos)
                    <h3 class="subtitulo-lora mt-5 mb-3">{{ __('Fotos') }}</h3>
                    <div class="fotos-destino">
                        @foreach ($fotos as $foto)
                            <img src="{{ asset($foto) }}" alt="Foto de {{ $nombre }}" loading="lazy">
                        @endforeach
                    </div>
                @endif

                @if ($videos)
                    <h3 class="subtitulo-lora mt-5 mb-3">{{ __('Videos') }}</h3>
                    <div class="row g-3">
                        @foreach ($videos as $video)
                            <div class="col-md-6">
                                <div class="ratio ratio-16x9 video-agencia">
                                    <iframe src="{{ $video }}" title="{{ __('Video de :nombre', ['nombre' => $nombre]) }}" loading="lazy"
                                            allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Contacto --}}
            <aside class="col-lg-4">
                <div class="caja-lateral">
                    <h3 class="caja-lateral-titulo">{{ __('Contacto') }}</h3>
                    <ul class="datos-agencia list-unstyled mb-4">
                        @if ($agencia->telefono)
                            <li><i class="bi bi-telephone"></i> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $agencia->telefono) }}">{{ $agencia->telefono }}</a></li>
                        @endif
                        <li><i class="bi bi-envelope"></i> <a href="mailto:{{ $agencia->correo }}">{{ $agencia->correo }}</a></li>
                        @if ($agencia->direccion)
                            <li><i class="bi bi-geo-alt"></i> {{ $agencia->direccion }}</li>
                        @endif
                        <li><i class="bi bi-compass"></i> {{ trans_choice(':count experiencia publicada|:count experiencias publicadas', $excursiones->count()) }}</li>
                    </ul>
                    <a href="{{ $agencia->enlaceWhatsapp() }}" class="btn btn-whatsapp-ancho w-100" target="_blank" rel="noopener">
                        <i class="bi bi-whatsapp"></i> {{ __('Escribir por WhatsApp') }}
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>

{{-- Catálogo de la agencia --}}
<section class="seccion seccion-clara">
    <div class="container">
        <div class="seccion-encabezado">
            <div>
                <span class="seccion-etiqueta">{{ __('Catálogo') }}</span>
                <h2 class="seccion-titulo">{{ __('Experiencias de :nombre', ['nombre' => $nombre]) }}</h2>
            </div>
        </div>
        <div class="row g-4">
            @forelse ($excursiones as $excursion)
                <div class="col-12 col-md-6 col-lg-4">
                    @include('partials.tarjeta-excursion', ['excursion' => $excursion])
                </div>
            @empty
                <div class="col-12"><div class="aviso-vacio"><i class="bi bi-compass"></i><p>{{ __('Esta agencia todavía no ha publicado experiencias.') }}</p></div></div>
            @endforelse
        </div>
    </div>
</section>

@endsection
