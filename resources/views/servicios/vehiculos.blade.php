@extends('layouts.app')

@section('titulo', __('Renta de vehículos en República Dominicana').' — Explora RD')
@section('descripcion', __('Pide un vehículo para moverte por República Dominicana: económico, jeepeta, minivan o con chofer. Recogida en aeropuertos y hoteles.'))

@section('contenido')

@include('partials.cabecera', [
    'foto' => 'img/renta-vehiculos.jpg',
    'etiqueta' => '<i class="bi bi-car-front"></i> '.e(__('Renta de vehículos')),
    'titulo' => __('Muévete con libertad por la isla'),
    'subtitulo' => __('Te conseguimos el vehículo ideal con empresas de alquiler de confianza. Recógelo en el aeropuerto o en tu hotel.'),
    'migas' => [__('Inicio') => route('inicio'), __('Renta de vehículos') => null],
])

<section class="seccion seccion-clara pt-5">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-7 col-xl-8">

                {{-- Tipos de vehículo --}}
                <span class="seccion-etiqueta">{{ __('Vehículos disponibles') }}</span>
                <h2 class="seccion-titulo mb-4">{{ __('Elige el que va con tu viaje') }}</h2>
                <div class="row g-3 mb-5">
                    @foreach (\App\Models\Solicitud::TIPOS_VEHICULO as $clave => $tipo)
                        <div class="col-sm-6">
                            <div class="tarjeta-vehiculo">
                                <i class="bi {{ $tipo['icono'] }}"></i>
                                <div class="flex-grow-1">
                                    <h3>{{ __($tipo['nombre']) }}</h3>
                                    <p class="tarjeta-vehiculo-pasajeros"><i class="bi bi-people"></i> {{ __('Hasta :n pasajeros', ['n' => $tipo['pasajeros']]) }}</p>
                                    <p>{{ __($tipo['detalle']) }}</p>
                                    <a href="{{ route('servicios.vehiculos', array_filter(['vehiculo' => $clave, 'lugar' => $previo['lugar'] ?: null, 'fecha' => $previo['fecha']])) }}#solicitar"
                                       class="enlace-flecha">{{ __('Pedir este') }} <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Consejos --}}
                <span class="seccion-etiqueta">{{ __('Antes de salir') }}</span>
                <h2 class="seccion-titulo mb-4">{{ __('Consejos para manejar en República Dominicana') }}</h2>
                <div class="row g-3 mb-5">
                    @foreach ([
                        ['bi-person-vcard', 'Lleva tus documentos', 'Tu licencia de conducir vigente y tu pasaporte o cédula. La empresa te dirá qué más necesita.'],
                        ['bi-shield-check', 'Pregunta por el seguro', 'Pide que te expliquen qué cubre el seguro antes de firmar el contrato.'],
                        ['bi-camera', 'Revisa el vehículo', 'Toma fotos del vehículo al recogerlo para evitar malentendidos al devolverlo.'],
                        ['bi-signpost-2', 'Maneja con calma', 'Se maneja por la derecha. Cuidado con los motores y respeta los límites de velocidad.'],
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

                {{-- Preguntas --}}
                <span class="seccion-etiqueta">{{ __('Preguntas frecuentes') }}</span>
                <div class="accordion acordeon mt-3" id="preguntasVehiculo">
                    @foreach ([
                        '¿Explora RD alquila los vehículos?' => 'No directamente. Te ponemos en contacto con empresas de alquiler de confianza y te ayudamos a comparar opciones.',
                        '¿Dónde puedo recoger el vehículo?' => 'En los principales aeropuertos del país, en la ciudad o en tu hotel, según la empresa.',
                        '¿Y si no quiero manejar?' => 'Elige «Con chofer»: un chofer local te lleva a donde quieras durante tu viaje.',
                    ] as $pregunta => $respuesta)
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#preguntaV{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ __($pregunta) }}
                                </button>
                            </h3>
                            <div id="preguntaV{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#preguntasVehiculo">
                                <div class="accordion-body">{{ __($respuesta) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <aside class="col-lg-5 col-xl-4">
                @include('servicios._formulario', ['servicio' => 'vehiculo'])
            </aside>
        </div>
    </div>
</section>

@endsection
