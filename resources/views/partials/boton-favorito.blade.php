{{--
    Botón de corazón ♥ para guardar una excursión en favoritos.
    Variables: $excursion, y opcionalmente $grande = true (botón con texto, para la ficha)
      - Turista: guarda o quita (public/js/app.js lo hace sin recargar la página)
      - Sin sesión: lleva a «Entrar» y luego vuelve aquí
      - Agencia o administrador: no se muestra
--}}
@php
    $usuario = auth()->user();
    $grande = $grande ?? false;
@endphp

@if (! $usuario)
    <a href="{{ route('login', ['volver' => request()->getRequestUri()]) }}"
       class="btn-favorito {{ $grande ? 'btn-favorito-grande' : '' }} encima-enlace"
       title="{{ __('Inicia sesión para guardar en favoritos') }}" aria-label="{{ __('Inicia sesión para guardar en favoritos') }}">
        <i class="bi bi-heart"></i>@if ($grande) <span>{{ __('Guardar en favoritos') }}</span>@endif
    </a>
@elseif ($usuario->esTurista())
    @php $esFavorita = in_array($excursion->id, $usuario->idsFavoritas()); @endphp
    <form method="POST" action="{{ route('favoritos.alternar', $excursion) }}" class="form-favorito encima-enlace">
        @csrf
        <button type="submit" class="btn-favorito {{ $grande ? 'btn-favorito-grande' : '' }} {{ $esFavorita ? 'activo' : '' }}"
                aria-pressed="{{ $esFavorita ? 'true' : 'false' }}"
                data-texto-guardar="{{ __('Guardar en favoritos') }}" data-texto-guardada="{{ __('En tus favoritos') }}"
                title="{{ $esFavorita ? __('Quitar de favoritos') : __('Guardar en favoritos') }}"
                aria-label="{{ $esFavorita ? __('Quitar de favoritos') : __('Guardar en favoritos') }}">
            <i class="bi {{ $esFavorita ? 'bi-heart-fill' : 'bi-heart' }}"></i>@if ($grande) <span>{{ $esFavorita ? __('En tus favoritos') : __('Guardar en favoritos') }}</span>@endif
        </button>
    </form>
@endif
