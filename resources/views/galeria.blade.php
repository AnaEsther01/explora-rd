@extends('layouts.app')

@section('titulo', __('Galería de fotos de República Dominicana').' — Explora RD')
@section('descripcion', __('Fotos de playas, montañas, ciudades y excursiones de República Dominicana.'))

@php
    // Botones del filtro: [clave, texto, icono]
    $filtros = [
        ['todas', __('Todas'), 'bi-grid-3x3-gap'],
        ['playa', __('Playa'), 'bi-water'],
        ['montaña', __('Montaña'), 'bi-tree'],
        ['ciudad', __('Ciudad'), 'bi-buildings'],
        ['agencia', __('Agencias'), 'bi-building'],
    ];
    $textoEnlace = ['destino' => __('Ver destino'), 'excursion' => __('Ver excursión'), 'agencia' => __('Ver agencia')];
@endphp

@section('contenido')

@include('partials.cabecera', [
    'foto' => 'img/destinos/samana.jpg',
    'etiqueta' => '<i class="bi bi-images"></i> '.e(__('Galería')),
    'titulo' => __('República Dominicana en imágenes'),
    'subtitulo' => __('Playas, montañas, ciudades y experiencias de todo el país. Toca una foto para verla en grande.'),
    'migas' => [__('Inicio') => route('inicio'), __('Galería') => null],
])

<section class="seccion seccion-clara pt-5">
    <div class="container">
        @if ($fotos->isEmpty())
            <div class="aviso-vacio"><i class="bi bi-images"></i><p>{{ __('Todavía no hay fotos en la galería.') }}</p></div>
        @else
            {{-- Filtro (funciona sin recargar la página, con public/js/galeria.js) --}}
            <div class="filtros-pastilla mb-3" role="group" aria-label="{{ __('Filtrar fotos') }}">
                @foreach ($filtros as [$clave, $texto, $icono])
                    @php $cantidad = $clave === 'todas' ? $fotos->count() : $fotos->filter(fn ($f) => $f['tipo'] === $clave || $f['grupo'] === $clave)->count(); @endphp
                    @if ($cantidad)
                        <button type="button" class="pastilla-filtro {{ $clave === 'todas' ? 'activa' : '' }}" data-filtro="{{ $clave }}"
                                aria-pressed="{{ $clave === 'todas' ? 'true' : 'false' }}">
                            <i class="bi {{ $icono }}"></i> {{ $texto }} <small>{{ $cantidad }}</small>
                        </button>
                    @endif
                @endforeach
            </div>
            <p class="texto-resultados" id="contadorGaleria" data-plantilla="{{ __(':count fotos') }}">{{ __(':count fotos', ['count' => $fotos->count()]) }}</p>

            {{-- Mosaico de fotos --}}
            <div class="mosaico" id="mosaicoGaleria">
                @foreach ($fotos as $i => $foto)
                    <button type="button" class="mosaico-pieza"
                            data-filtros="{{ trim(($foto['tipo'] ?? '').' '.$foto['grupo']) }}"
                            data-src="{{ $foto['src'] }}" data-titulo="{{ $foto['titulo'] }}" data-lugar="{{ $foto['lugar'] }}"
                            data-enlace="{{ $foto['enlace'] }}" data-texto-enlace="{{ $textoEnlace[$foto['grupo']] }}"
                            @if ($foto['credito'])
                                data-autor="{{ $foto['credito']['autor'] }}" data-fuente="{{ $foto['credito']['fuente'] }}" data-url-credito="{{ $foto['credito']['url'] }}"
                            @endif
                            aria-label="{{ __('Ver en grande: :titulo', ['titulo' => $foto['titulo']]) }}">
                        <img src="{{ $foto['src'] }}" alt="{{ $foto['titulo'] }}{{ $foto['lugar'] ? ', '.$foto['lugar'] : '' }}" loading="lazy">
                        <span class="mosaico-texto">
                            <strong>{{ $foto['titulo'] }}</strong>
                            @if ($foto['lugar']) <small>{{ $foto['lugar'] }}</small> @endif
                        </span>
                        <i class="bi bi-arrows-fullscreen mosaico-lupa" aria-hidden="true"></i>
                    </button>
                @endforeach
            </div>

            <p class="small text-muted mt-4 mb-0">
                <i class="bi bi-camera"></i> {{ __('Las fotos son de sus autores, que aparecen al ver cada foto en grande. Fotos de uso libre de Unsplash y Pexels.') }}
            </p>
        @endif
    </div>
</section>

{{-- ===== Visor para ver las fotos en grande ===== --}}
<div class="modal fade visor" id="visorGaleria" tabindex="-1" aria-labelledby="visorTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <button type="button" class="visor-boton visor-cerrar" data-bs-dismiss="modal" aria-label="{{ __('Cerrar') }}"><i class="bi bi-x-lg"></i></button>
            {{-- Las flechas van dentro del área de la foto para quedar siempre en su mitad --}}
            <div class="visor-foto">
                <img id="visorImagen" src="" alt="">
                <button type="button" class="visor-boton visor-anterior" id="visorAnterior" aria-label="{{ __('Foto anterior') }}"><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="visor-boton visor-siguiente" id="visorSiguiente" aria-label="{{ __('Foto siguiente') }}"><i class="bi bi-chevron-right"></i></button>
            </div>

            <div class="visor-pie">
                <div>
                    <h2 id="visorTitulo"></h2>
                    <p id="visorLugar"></p>
                    <p class="visor-credito" id="visorCredito"></p>
                </div>
                <div class="visor-acciones">
                    <span id="visorContador"></span>
                    <a href="#" id="visorEnlace" class="btn btn-turquesa btn-sm"></a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>window.TEXTOS_GALERIA = {!! json_encode(['foto' => __('Foto')], JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) !!};</script>
    <script src="{{ asset('js/galeria.js') }}"></script>
@endpush

@endsection
