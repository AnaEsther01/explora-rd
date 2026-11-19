{{--
    Mapa interactivo con los destinos (Leaflet + OpenStreetMap: gratis y sin cuenta).
    Variables:
      $destinos  colección de destinos (solo salen los que tienen latitud y longitud)
      $alto      (opcional) clase de altura: 'mapa-grande' o 'mapa-pequeno'
      $zoom      (opcional) acercamiento cuando hay un solo destino (por defecto 10)

    La librería Leaflet se carga desde internet (CDN), igual que Bootstrap: no se instala nada.
    @once hace que se cargue una sola vez aunque haya dos mapas en la misma página.
--}}
@php
    $puntos = $destinos->filter->tieneUbicacion()->map->datosMapa()->values();
@endphp

@once
    @push('estilos')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script src="{{ asset('js/mapa.js') }}"></script>
    @endpush
@endonce

@if ($puntos->isNotEmpty())
    <div class="mapa-explora {{ $alto ?? 'mapa-grande' }}" role="region" aria-label="{{ __('Mapa de destinos') }}"
         data-destinos="{{ $puntos->toJson(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
         data-zoom="{{ $zoom ?? 10 }}"
         data-texto-ver="{{ __('Ver destino') }}">
        {{-- Mientras carga (o si no hay internet) se ve este aviso --}}
        <p class="mapa-cargando"><i class="bi bi-map"></i> {{ __('Cargando el mapa...') }}</p>
    </div>
@endif
