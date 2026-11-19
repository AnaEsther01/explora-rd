@extends('layouts.app')

@section('titulo', __('Excursiones y experiencias en República Dominicana').' — Explora RD')
@section('descripcion', __('Excursiones a playas, montaña, catamarán, cultura, gastronomía, familia y aventura en República Dominicana, organizadas por agencias locales.'))

@php
    // Enlace al catálogo cambiando solo un filtro y manteniendo los demás
    $conFiltro = fn (array $cambios) => route('excursiones.index', array_filter(
        array_merge(request()->except('page'), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $tipoActual = $filtros['tipo'] ?? '';
    $hayFiltros = collect($filtros)->except(['orden', 'fecha', 'personas'])->filter()->isNotEmpty();
@endphp

@section('contenido')

@include('partials.cabecera', [
    'foto' => 'img/excursiones/catamaran-saona.jpg',
    'etiqueta' => e(__('Excursiones y experiencias')),
    'titulo' => __('Vive República Dominicana'),
    'subtitulo' => __('Playas, montaña, catamarán, cultura, gastronomía, familia y aventura con agencias locales.'),
    'migas' => [__('Inicio') => route('inicio'), __('Excursiones') => null],
])

<section class="seccion seccion-clara pt-5">
    <div class="container">

        {{-- Categorías (acceso rápido) --}}
        <div class="filtros-pastilla mb-4" role="group" aria-label="{{ __('Categorías') }}">
            <a href="{{ $conFiltro(['categoria' => null]) }}" class="pastilla-filtro {{ $categoriaActual ? '' : 'activa' }}">
                <i class="bi bi-grid"></i> {{ __('Todas') }}
            </a>
            @foreach (\App\Models\Excursion::CATEGORIAS as $clave => $cat)
                <a href="{{ $conFiltro(['categoria' => $clave]) }}" class="pastilla-filtro {{ $categoriaActual === $clave ? 'activa' : '' }}">
                    <i class="bi {{ $cat['icono'] }}"></i> {{ __($cat['nombre']) }}
                </a>
            @endforeach
        </div>

        <div class="row g-4">
            {{-- ===== Filtros ===== --}}
            <aside class="col-lg-3">
                <button class="btn btn-contorno w-100 d-lg-none mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#panelFiltros"
                        aria-expanded="false" aria-controls="panelFiltros">
                    <i class="bi bi-sliders"></i> {{ __('Filtros') }}
                </button>

                <form id="panelFiltros" class="caja-lateral collapse d-lg-block" action="{{ route('excursiones.index') }}" method="GET">
                    @if ($categoriaActual)
                        <input type="hidden" name="categoria" value="{{ $categoriaActual }}">
                    @endif

                    <h2 class="caja-lateral-titulo">{{ __('Buscar') }}</h2>

                    <div class="mb-3">
                        <label for="filtroDestino" class="form-label">{{ __('Destino') }}</label>
                        <div class="campo-icono">
                            <i class="bi bi-geo-alt-fill"></i>
                            <input type="text" class="form-control" id="filtroDestino" name="destino" list="listaDestinos"
                                   value="{{ $filtros['destino'] ?? '' }}" placeholder="{{ __('¿A dónde quieres ir?') }}">
                            <datalist id="listaDestinos">
                                @foreach ($listaDestinos as $nombre)
                                    <option value="{{ $nombre }}">
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="filtroTipo" class="form-label">{{ __('Tipo') }}</label>
                        <select class="form-select" id="filtroTipo" name="tipo">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach (\App\Models\Excursion::TIPOS as $clave => $nombre)
                                <option value="{{ $clave }}" @selected($tipoActual === $clave)>{{ __($nombre) }}</option>
                            @endforeach
                            <option value="circuitos" @selected($tipoActual === 'circuitos')>{{ __('Circuitos y paquetes') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="filtroPrecio" class="form-label d-flex justify-content-between">
                            <span>{{ __('Precio máximo') }}</span>
                            <output id="valorPrecio" class="fw-semibold text-turquesa">
                                {{ ! empty($filtros['precio']) ? 'RD$ '.number_format($filtros['precio']) : __('Cualquiera') }}
                            </output>
                        </label>
                        <input type="range" class="form-range" id="filtroPrecio" name="precio"
                               min="1000" max="{{ max($precioMaximo, 1000) }}" step="500"
                               value="{{ $filtros['precio'] ?? max($precioMaximo, 1000) }}">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label for="filtroFecha" class="form-label">{{ __('Fecha') }}</label>
                            <input type="date" class="form-control" id="filtroFecha" name="fecha" min="{{ date('Y-m-d') }}" value="{{ $filtros['fecha'] ?? '' }}">
                        </div>
                        <div class="col-5">
                            <label for="filtroPersonas" class="form-label">{{ __('Personas') }}</label>
                            <select class="form-select" id="filtroPersonas" name="personas">
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" @selected((int) ($filtros['personas'] ?? 2) === $i)>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-turquesa w-100"><i class="bi bi-search"></i> {{ __('Aplicar filtros') }}</button>
                    @if ($hayFiltros)
                        <a href="{{ route('excursiones.index') }}" class="btn btn-link w-100 mt-1 enlace-limpiar">{{ __('Quitar filtros') }}</a>
                    @endif
                </form>
            </aside>

            {{-- ===== Resultados ===== --}}
            <div class="col-lg-9">
                <div class="barra-resultados">
                    <p class="texto-resultados mb-0">
                        {!! trans_choice('<strong>:count</strong> experiencia encontrada|<strong>:count</strong> experiencias encontradas', $excursiones->total()) !!}
                        @if (! empty($filtros['destino'])) · <strong>«{{ $filtros['destino'] }}»</strong> @endif
                        @if ($categoriaActual) · {{ __(\App\Models\Excursion::CATEGORIAS[$categoriaActual]['nombre']) }} @endif
                    </p>
                    <form action="{{ route('excursiones.index') }}" method="GET" class="d-flex align-items-center gap-2">
                        @foreach (request()->except(['orden', 'page']) as $nombre => $valor)
                            <input type="hidden" name="{{ $nombre }}" value="{{ $valor }}">
                        @endforeach
                        <label for="orden" class="small text-nowrap">{{ __('Ordenar por') }}</label>
                        <select id="orden" name="orden" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="recomendados" @selected(($filtros['orden'] ?? '') === 'recomendados')>{{ __('Recomendados') }}</option>
                            <option value="precio_menor" @selected(($filtros['orden'] ?? '') === 'precio_menor')>{{ __('Precio: menor a mayor') }}</option>
                            <option value="precio_mayor" @selected(($filtros['orden'] ?? '') === 'precio_mayor')>{{ __('Precio: mayor a menor') }}</option>
                            <option value="valoradas" @selected(($filtros['orden'] ?? '') === 'valoradas')>{{ __('Mejor valoradas') }}</option>
                        </select>
                    </form>
                </div>

                <div class="row g-4">
                    @forelse ($excursiones as $excursion)
                        <div class="col-12 col-md-6 col-xl-4">
                            @include('partials.tarjeta-excursion', [
                                'excursion' => $excursion,
                                'fecha' => $filtros['fecha'] ?? null,
                                'personas' => $filtros['personas'] ?? null,
                            ])
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="aviso-vacio">
                                <i class="bi bi-search"></i>
                                <p>{{ __('No encontramos experiencias con esos filtros.') }}<br>{{ __('Prueba con otro destino o quita algunos filtros.') }}</p>
                                <a href="{{ route('excursiones.index') }}" class="btn btn-turquesa">{{ __('Ver todas las excursiones') }}</a>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-5 d-flex justify-content-center">
                    {{ $excursiones->links() }}
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
