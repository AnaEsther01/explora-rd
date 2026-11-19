{{-- Dibuja 5 estrellas: llenas, media y vacías. Variable: $valor (ej.: 4.5) --}}
@php
    $valor = max(0, min(5, (float) $valor));
    $llenas = (int) floor($valor);
    $media = ($valor - $llenas) >= 0.5;
@endphp
<span class="estrellas" role="img" aria-label="{{ __(':valor de 5 estrellas', ['valor' => $valor]) }}">
    @for ($i = 1; $i <= 5; $i++)
        <i class="bi {{ $i <= $llenas ? 'bi-star-fill' : ($i === $llenas + 1 && $media ? 'bi-star-half' : 'bi-star') }}" aria-hidden="true"></i>
    @endfor
</span>
