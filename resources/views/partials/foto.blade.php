{{--
    Muestra una foto si existe en public/img. Si todavía no existe,
    muestra un dibujo de marcador de posición (playa, montaña o ciudad).
    Variables: $ruta (ej.: img/destinos/jarabacoa.jpg), $tipo (playa, montaña, ciudad), $alt, $icono (opcional)
--}}
@php
    $tipo = $tipo ?? 'playa';
    $marcadores = ['playa' => 'playa.svg', 'montaña' => 'montana.svg', 'ciudad' => 'ciudad.svg'];
    $existe = ! empty($ruta) && file_exists(public_path($ruta));
@endphp

@if ($existe)
    <img src="{{ asset($ruta) }}" alt="{{ $alt ?? '' }}" class="foto-img" loading="lazy">
@else
    <div class="foto-ph">
        <img src="{{ asset('img/marcadores/'.($marcadores[$tipo] ?? 'playa.svg')) }}" alt="{{ $alt ?? 'Foto' }}" class="foto-img" loading="lazy">
        @isset($icono)
            <span class="foto-ph-icono" aria-hidden="true">{{ $icono }}</span>
        @endisset
    </div>
@endif
