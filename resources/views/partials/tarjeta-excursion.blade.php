{{-- Tarjeta de una excursión, circuito o paquete.
     Variables: $excursion, y opcionalmente $fecha y $personas (del buscador) --}}
@php
    // «Bayahíbe, La Altagracia» → «Bayahíbe»
    $salida = \Illuminate\Support\Str::before($excursion->ubicacion ?? $excursion->destino->nombre, ',');
    $datosBusqueda = array_filter(['fecha' => $fecha ?? null, 'personas' => $personas ?? null]);
    $urlFicha = route('excursiones.show', ['excursion' => $excursion] + $datosBusqueda);
@endphp

<article class="tarjeta-excursion">
    <div class="tarjeta-foto">
        @include('partials.foto', ['ruta' => $excursion->foto, 'tipo' => $excursion->destino->tipo, 'alt' => $excursion->titulo])
        <span class="etiqueta-salida"><i class="bi bi-bus-front"></i> {{ __('Sale desde :lugar', ['lugar' => $salida]) }}</span>
        <span class="etiqueta-lugar">{{ $excursion->destino->nombre }}</span>
        <div class="tarjeta-favorito">@include('partials.boton-favorito', ['excursion' => $excursion])</div>
    </div>

    <div class="tarjeta-excursion-texto">
        <p class="tarjeta-destino-nombre">
            {{ $excursion->nombreTipo() }}@if ($excursion->nombreCategoria()) · {{ $excursion->nombreCategoria() }}@endif
        </p>
        <h3><a href="{{ $urlFicha }}" class="stretched-link">{{ $excursion->titulo }}</a></h3>

        @if ($excursion->totalResenas())
            <p class="tarjeta-valoracion">
                <i class="bi bi-star-fill"></i> <strong>{{ number_format($excursion->promedioEstrellas(), 1) }}</strong>
                <span>({{ trans_choice(':count reseña|:count reseñas', $excursion->totalResenas()) }})</span>
            </p>
        @endif

        <ul class="tarjeta-datos list-unstyled">
            @if ($excursion->duracion)
                <li><i class="bi bi-clock"></i> {{ $excursion->duracion }}</li>
            @endif
            <li><i class="bi bi-building"></i> {{ $excursion->agencia->nombre_comercial ?? $excursion->agencia->nombre }}</li>
        </ul>

        <div class="tarjeta-pie">
            <div class="precio">
                <small>{{ __('Desde') }}</small>
                <strong>{{ $excursion->precioFormateado() }}</strong>
                <small>{{ __('por persona') }}</small>
            </div>
            <a href="{{ $excursion->enlaceWhatsapp() }}" class="btn btn-whatsapp encima-enlace"
               target="_blank" rel="noopener" aria-label="{{ __('Contactar a la agencia por WhatsApp') }}" title="{{ __('Escribir a la agencia') }}">
                <i class="bi bi-whatsapp"></i>
            </a>
        </div>
    </div>
</article>
