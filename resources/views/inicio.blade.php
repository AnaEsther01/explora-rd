@extends('layouts.app')

@php
    // Si existe public/img/portada.jpg se usa esa foto de fondo;
    // si no, se usa el dibujo provisional public/img/portada-ilustracion.svg
    $fondoPortada = file_exists(public_path('img/portada.jpg')) ? 'img/portada.jpg' : 'img/portada-ilustracion.svg';
    $whatsapp = config('explora.whatsapp');
    // Todos los textos van dentro de __('...'): así se traducen con lang/en.json y lang/fr.json
@endphp

@section('contenido')

{{-- =========================================================
     1. PORTADA: foto tropical, título, pestañas y buscador flotante
     ========================================================= --}}
<section class="portada" style="--foto-portada: url('{{ asset($fondoPortada) }}')">

    <div class="container portada-contenido">
        <h1 class="portada-titulo">{{ __('Descubre República Dominicana') }}</h1>
        <p class="portada-subtitulo">{{ __('Experiencias personalizadas · Playas, montañas y cultura') }}</p>

        <div class="buscador-caja">
            {{-- Pestañas de categorías --}}
            <ul class="nav pestanas" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="pestana active" type="button" data-categoria="experiencias" role="tab" aria-selected="true">
                        <i class="bi bi-compass"></i> {{ __('Experiencias') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="pestana" type="button" data-categoria="circuitos" role="tab" aria-selected="false">
                        <i class="bi bi-signpost-split"></i> {{ __('Circuitos y paquetes') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="pestana" type="button" data-categoria="asesorias" role="tab" aria-selected="false">
                        <i class="bi bi-chat-heart"></i> {{ __('Asesorías') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="pestana" type="button" data-categoria="vehiculos" role="tab" aria-selected="false">
                        <i class="bi bi-car-front"></i> {{ __('Renta de vehículos') }}
                    </button>
                </li>
            </ul>

            {{-- Buscador blanco: lleva al catálogo de excursiones con los filtros elegidos.
                 En «Asesorías» y «Renta de vehículos» lleva a su página con el formulario ya lleno. --}}
            <form id="buscador" class="buscador" action="{{ route('excursiones.index') }}" method="GET" novalidate
                  data-url-asesorias="{{ route('servicios.asesorias') }}" data-url-vehiculos="{{ route('servicios.vehiculos') }}">
                <input type="hidden" name="tipo" id="campoTipo" value="" disabled>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="campoDestino" class="form-label" id="etiquetaDestino">{{ __('Destino') }}</label>
                        <div class="campo-icono">
                            <i class="bi bi-geo-alt-fill"></i>
                            <input type="text" class="form-control" id="campoDestino" name="destino"
                                   list="listaDestinos" placeholder="{{ __('¿A dónde quieres ir?') }}" autocomplete="off">
                            <datalist id="listaDestinos">
                                @foreach ($listaDestinos as $nombre)
                                    <option value="{{ $nombre }}">
                                @endforeach
                            </datalist>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="campoFecha" class="form-label" id="etiquetaFecha">{{ __('Fechas') }}</label>
                        <div class="campo-icono">
                            <i class="bi bi-calendar3"></i>
                            <input type="date" class="form-control" id="campoFecha" name="fecha" min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="campoPersonas" class="form-label" id="etiquetaPersonas">{{ __('Personas') }}</label>
                        <div class="campo-icono">
                            <i class="bi bi-people-fill"></i>
                            <select class="form-select" id="campoPersonas" name="personas">
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" @selected($i === 2)>{{ trans_choice(':count adulto|:count adultos', $i) }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-turquesa w-100 btn-buscar">
                            <i class="bi bi-search"></i> {{ __('Buscar') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- =========================================================
     2. EXCURSIONES Y EXPERIENCIAS DESTACADAS
     ========================================================= --}}
<section id="excursiones" class="seccion seccion-clara">
    <div class="container">
        <div class="seccion-encabezado">
            <div>
                <span class="seccion-etiqueta">{{ __('Destacados') }}</span>
                <h2 class="seccion-titulo">{{ __('Experiencias que enamoran') }} <i class="bi bi-chevron-right titulo-flecha"></i></h2>
                <p class="seccion-texto">{{ __('Las excursiones mejor valoradas por nuestros viajeros.') }}</p>
            </div>
            <a href="{{ route('excursiones.index') }}" class="enlace-ver-todo">{{ __('Ver todos los tours') }} <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-4">
            @forelse ($excursiones as $excursion)
                <div class="col-12 col-md-6 col-lg-4">
                    @include('partials.tarjeta-excursion', ['excursion' => $excursion])
                </div>
            @empty
                <p class="text-muted">{{ __('Todavía no hay excursiones publicadas.') }}</p>
            @endforelse
        </div>
    </div>
</section>

{{-- =========================================================
     3. DESTINOS DESTACADOS
     ========================================================= --}}
<section id="destinos" class="seccion">
    <div class="container">
        <div class="seccion-encabezado">
            <div>
                <span class="seccion-etiqueta">{{ __('Destinos') }}</span>
                <h2 class="seccion-titulo">{{ __('Lugares que enamoran') }}</h2>
                <p class="seccion-texto">{{ __('Provincias y rincones de República Dominicana que tienes que conocer.') }}</p>
            </div>
            <a href="{{ route('destinos.index') }}" class="enlace-ver-todo">{{ __('Ver todos los destinos') }} <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-4">
            @forelse ($destinos as $destino)
                <div class="col-12 col-sm-6 col-lg-3">
                    @include('partials.tarjeta-destino', ['destino' => $destino])
                </div>
            @empty
                <p class="text-muted">{{ __('Todavía no hay destinos registrados.') }}</p>
            @endforelse
        </div>
    </div>
</section>

{{-- =========================================================
     4. CIRCUITOS Y PAQUETES
     ========================================================= --}}
<section id="circuitos" class="seccion">
    <div class="container">
        <div class="seccion-encabezado">
            <div>
                <span class="seccion-etiqueta">{{ __('Circuitos y paquetes') }}</span>
                <h2 class="seccion-titulo">{{ __('Conoce varios lugares en un mismo viaje') }}</h2>
                <p class="seccion-texto">{{ __('Rutas organizadas por agencias locales, con transporte y guía incluidos.') }}</p>
            </div>
            <a href="{{ route('excursiones.index', ['tipo' => 'circuitos']) }}" class="enlace-ver-todo">{{ __('Ver todos los paquetes') }} <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-4">
            @forelse ($circuitos as $circuito)
                <div class="col-12 col-md-6 col-lg-4">
                    @include('partials.tarjeta-excursion', ['excursion' => $circuito])
                </div>
            @empty
                <p class="text-muted">{{ __('Todavía no hay circuitos ni paquetes publicados.') }}</p>
            @endforelse
        </div>
    </div>
</section>

{{-- =========================================================
     5. ASESORÍAS Y RENTA DE VEHÍCULOS
     ========================================================= --}}
<section id="servicios" class="seccion seccion-arena">
    <div class="container">
        <div class="text-center mb-5">
            <span class="seccion-etiqueta">{{ __('Servicios especiales') }}</span>
            <h2 class="seccion-titulo">{{ __('Te ayudamos a organizar tu viaje') }}</h2>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="tarjeta-servicio">
                    <div class="tarjeta-foto">
                        @include('partials.foto', ['ruta' => 'img/asesorias.jpg', 'tipo' => 'ciudad', 'icono' => '🗺️', 'alt' => __('Asesorías de viaje')])
                    </div>
                    <div class="tarjeta-servicio-texto">
                        <h3><i class="bi bi-chat-heart"></i> {{ __('Asesorías') }}</h3>
                        <p>{{ __('Orientación para organizar tu viaje, elegir destinos y encontrar las experiencias que van contigo.') }}</p>
                        <a href="{{ route('servicios.asesorias') }}" class="btn btn-turquesa">
                            <i class="bi bi-chat-heart"></i> {{ __('Pedir asesoría') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="tarjeta-servicio">
                    <div class="tarjeta-foto">
                        @include('partials.foto', ['ruta' => 'img/renta-vehiculos.jpg', 'tipo' => 'playa', 'icono' => '🚙', 'alt' => __('Renta de vehículos')])
                    </div>
                    <div class="tarjeta-servicio-texto">
                        <h3><i class="bi bi-car-front"></i> {{ __('Renta de vehículos') }}</h3>
                        <p>{{ __('Información sobre vehículos disponibles y empresas de alquiler para moverte con libertad por la isla.') }}</p>
                        <a href="{{ route('servicios.vehiculos') }}" class="btn btn-turquesa">
                            <i class="bi bi-car-front"></i> {{ __('Consultar vehículos') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     6. SOBRE NOSOTROS Y POR QUÉ ELEGIRNOS
     ========================================================= --}}
<section id="nosotros" class="seccion">
    <div class="container">
        <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-lg-5">
                <span class="seccion-etiqueta">{{ __('Sobre nosotros') }}</span>
                <h2 class="seccion-titulo">{{ __('Un solo lugar para descubrir República Dominicana') }}</h2>
                <p class="seccion-texto">{{ __('Explora RD nace para que cualquier persona pueda descubrir lugares increíbles, encontrar excursiones y contactar directamente con agencias turísticas, desde la computadora o el celular.') }}</p>
                <p class="seccion-texto">{{ __('Queremos que los turistas vivan experiencias auténticas y que las agencias locales tengan un espacio para promocionarse sin crear una página web desde cero.') }}</p>
            </div>
            <div class="col-lg-7">
                <h3 class="subtitulo-lora mb-4">{{ __('¿Por qué elegirnos?') }}</h3>
                <div class="row g-3">
                    @foreach ([
                        ['bi-patch-check', 'Agencias locales', 'Trata directamente con agencias dominicanas, sin intermediarios.'],
                        ['bi-whatsapp', 'Contacto directo', 'Escribe por WhatsApp y resuelve tus dudas al momento.'],
                        ['bi-map', 'Todo el país', 'Playas, montañas y ciudades de todas las regiones en un solo sitio.'],
                        ['bi-phone', 'Desde tu celular', 'Busca, compara y reserva desde cualquier dispositivo.'],
                    ] as [$icono, $titulo, $texto])
                        <div class="col-sm-6">
                            <div class="beneficio">
                                <i class="bi {{ $icono }}"></i>
                                <h4>{{ __($titulo) }}</h4>
                                <p>{{ __($texto) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     7. ESPACIO PARA AGENCIAS
     ========================================================= --}}
<section id="agencias" class="seccion-agencias">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="seccion-etiqueta etiqueta-clara">{{ __('Para agencias turísticas') }}</span>
                <h2 class="seccion-titulo text-white">{{ __('¿Tienes una agencia? Promociónate en Explora RD') }}</h2>
                <p class="mb-0 texto-claro">{{ __('Crea tu perfil con logo, fotos y videos, publica tus excursiones y paquetes con precios y disponibilidad, recibe clientes por WhatsApp y gestiona tus reservas desde tu propio panel.') }}</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('registro', ['tipo' => 'agencia']) }}" class="btn btn-arena btn-lg">{{ __('Únete como agencia') }}</a>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     8. GALERÍA
     ========================================================= --}}
<section id="galeria" class="seccion">
    <div class="container">
        <div class="text-center mb-5">
            <span class="seccion-etiqueta">{{ __('Galería') }}</span>
            <h2 class="seccion-titulo">{{ __('República Dominicana en imágenes') }}</h2>
            <p class="seccion-texto">{{ __('Un recorrido por las provincias del país.') }}</p>
        </div>
        {{-- Las fotos vienen de los destinos guardados en la base de datos --}}
        <div class="galeria">
            @foreach ($galeria as $i => $destino)
                <a href="{{ route('destinos.show', $destino) }}" class="galeria-item galeria-item-{{ $i + 1 }}">
                    @include('partials.foto', ['ruta' => $destino->foto, 'tipo' => $destino->tipo, 'alt' => $destino->nombre.', '.$destino->provincia])
                    <span class="galeria-nombre">{{ $destino->nombre }} <small>{{ $destino->provincia }}</small></span>
                </a>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('galeria') }}" class="btn btn-contorno"><i class="bi bi-images"></i> {{ __('Ver toda la galería') }}</a>
        </div>
    </div>
</section>

{{-- =========================================================
     9. PREGUNTAS FRECUENTES
     ========================================================= --}}
<section id="preguntas" class="seccion seccion-arena">
    <div class="container contenedor-estrecho">
        <div class="text-center mb-5">
            <span class="seccion-etiqueta">{{ __('Preguntas frecuentes') }}</span>
            <h2 class="seccion-titulo">{{ __('¿Tienes dudas?') }}</h2>
        </div>

        @php
            $preguntas = [
                ['¿Cómo reservo una excursión?', 'Entra a la ficha de la excursión, elige la fecha y la cantidad de personas y envía tu solicitud. La agencia la revisa y te confirma la reserva. También puedes escribirle directamente por WhatsApp.'],
                ['¿Tengo que pagar en Explora RD?', 'No. Explora RD te pone en contacto con la agencia; el pago y las condiciones los acuerdas directamente con ella.'],
                ['¿Puedo cancelar una reserva?', 'Sí. Cada agencia tiene su propia política de cancelación; te recomendamos consultarla antes de reservar.'],
                ['¿Cómo publico mi agencia en Explora RD?', 'Regístrate como agencia, completa tu perfil con tu logo, fotos y datos de contacto, y empieza a publicar tus excursiones y paquetes.'],
            ];
        @endphp

        <div class="accordion acordeon" id="acordeonPreguntas">
            @foreach ($preguntas as $i => [$pregunta, $respuesta])
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button {{ $i ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse"
                                data-bs-target="#pregunta{{ $i }}" aria-expanded="{{ $i ? 'false' : 'true' }}" aria-controls="pregunta{{ $i }}">
                            {{ __($pregunta) }}
                        </button>
                    </h3>
                    <div id="pregunta{{ $i }}" class="accordion-collapse collapse {{ $i ? '' : 'show' }}" data-bs-parent="#acordeonPreguntas">
                        <div class="accordion-body">{{ __($respuesta) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- =========================================================
     10. CONTACTO
     ========================================================= --}}
<section id="contacto" class="seccion">
    <div class="container">
        <div class="text-center mb-5">
            <span class="seccion-etiqueta">{{ __('Contacto') }}</span>
            <h2 class="seccion-titulo">{{ __('Hablemos de tu próximo viaje') }}</h2>
            <p class="seccion-texto">{{ __('Escríbenos y te ayudamos a encontrar la experiencia perfecta.') }}</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <a href="https://wa.me/{{ $whatsapp }}" class="tarjeta-contacto" target="_blank" rel="noopener">
                    <i class="bi bi-whatsapp"></i>
                    <h3>WhatsApp</h3>
                    <p>{{ config('explora.telefono') }}</p>
                </a>
            </div>
            <div class="col-md-4">
                <a href="mailto:{{ config('explora.correo') }}" class="tarjeta-contacto">
                    <i class="bi bi-envelope"></i>
                    <h3>{{ __('Correo') }}</h3>
                    <p>{{ config('explora.correo') }}</p>
                </a>
            </div>
            <div class="col-md-4">
                <div class="tarjeta-contacto">
                    <i class="bi bi-geo-alt"></i>
                    <h3>{{ __('Ubicación') }}</h3>
                    <p>{{ __(config('explora.direccion')) }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
