{{-- Tarjeta de un destino. Variable: $destino (puede traer excursiones_count) --}}
<a href="{{ route('destinos.show', $destino) }}" class="tarjeta-destino">
    <div class="tarjeta-foto">
        @include('partials.foto', ['ruta' => $destino->foto, 'tipo' => $destino->tipo, 'alt' => $destino->nombre])
        <span class="etiqueta-tipo">{{ __(\App\Models\Destino::TIPOS[$destino->tipo]['nombre'] ?? ucfirst($destino->tipo)) }}</span>
    </div>
    <div class="tarjeta-destino-texto">
        <h3>{{ $destino->nombre }}</h3>
        <p><i class="bi bi-geo-alt"></i> {{ $destino->provincia }}</p>
        <p class="tarjeta-resumen">{{ $destino->resumen() }}</p>
        @isset($destino->excursiones_count)
            <p class="tarjeta-contador">
                <i class="bi bi-compass"></i>
                {{ trans_choice(':count experiencia|:count experiencias', $destino->excursiones_count) }}
                <i class="bi bi-arrow-right ms-auto"></i>
            </p>
        @endisset
    </div>
</a>
