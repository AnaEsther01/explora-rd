{{--
    Cabecera con foto para las páginas internas (destinos, excursiones...).
    Variables:
      $foto      ruta de la foto en public/img (si no existe, usa la portada)
      $titulo    título grande
      $subtitulo (opcional) texto debajo del título
      $etiqueta  (opcional) texto pequeño encima del título
      $migas     (opcional) ['Inicio' => url, 'Destinos' => url, 'Jarabacoa' => null]
      $alta      (opcional) true para una cabecera más alta (páginas de detalle)
--}}
@php
    $rutaFoto = (! empty($foto) && file_exists(public_path($foto))) ? $foto
        : (file_exists(public_path('img/portada.jpg')) ? 'img/portada.jpg' : 'img/portada-ilustracion.svg');
@endphp

<header class="cabecera {{ ! empty($alta) ? 'cabecera-alta' : '' }}" style="--foto-cabecera: url('{{ asset($rutaFoto) }}')">
    <div class="container cabecera-contenido">
        @if (! empty($migas))
            <nav aria-label="{{ __('Ruta de navegación') }}">
                <ol class="breadcrumb migas">
                    @foreach ($migas as $texto => $url)
                        @if ($url)
                            <li class="breadcrumb-item"><a href="{{ $url }}">{{ $texto }}</a></li>
                        @else
                            <li class="breadcrumb-item active" aria-current="page">{{ $texto }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif

        @if (! empty($etiqueta))
            <span class="cabecera-etiqueta">{!! $etiqueta !!}</span>
        @endif

        <h1 class="cabecera-titulo">{{ $titulo }}</h1>

        @if (! empty($subtitulo))
            <p class="cabecera-subtitulo">{{ $subtitulo }}</p>
        @endif

        {{ $slot ?? '' }}
    </div>
</header>
