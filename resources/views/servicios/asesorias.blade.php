@extends('layouts.app')

@section('titulo', __('Asesorías de viaje en República Dominicana').' — Explora RD')
@section('descripcion', __('Te ayudamos gratis a organizar tu viaje por República Dominicana: destinos, excursiones, agencias y presupuesto.'))

@section('contenido')

@include('partials.cabecera', [
    'foto' => 'img/asesorias.jpg',
    'etiqueta' => '<i class="bi bi-chat-heart"></i> '.e(__('Asesorías')),
    'titulo' => __('Organiza tu viaje con nuestra ayuda'),
    'subtitulo' => __('Cuéntanos qué te gusta y te ayudamos a elegir destinos, excursiones y agencias de confianza.'),
    'migas' => [__('Inicio') => route('inicio'), __('Asesorías') => null],
])

<section class="seccion seccion-clara pt-5">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-7 col-xl-8">

                {{-- Cómo funciona --}}
                <span class="seccion-etiqueta">{{ __('Cómo funciona') }}</span>
                <h2 class="seccion-titulo mb-4">{{ __('Tres pasos y listo') }}</h2>
                <div class="row g-3 mb-5">
                    @foreach ([
                        ['1', 'bi-pencil-square', 'Cuéntanos tu viaje', 'Llena el formulario con el destino, las fechas y lo que te gusta hacer.'],
                        ['2', 'bi-whatsapp', 'Te contactamos', 'Un asesor de Explora RD te escribe por WhatsApp o te llama.'],
                        ['3', 'bi-map', 'Recibe tu plan', 'Te recomendamos destinos, excursiones y agencias según tu presupuesto.'],
                    ] as [$numero, $icono, $titulo, $texto])
                        <div class="col-md-4">
                            <div class="paso-servicio">
                                <span class="paso-numero">{{ $numero }}</span>
                                <i class="bi {{ $icono }}"></i>
                                <h3>{{ __($titulo) }}</h3>
                                <p>{{ __($texto) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- En qué te ayudamos --}}
                <span class="seccion-etiqueta">{{ __('En qué te ayudamos') }}</span>
                <h2 class="seccion-titulo mb-4">{{ __('Un viaje a tu medida') }}</h2>
                <div class="row g-3 mb-5">
                    @foreach ([
                        ['bi-geo-alt', 'Elegir destinos', 'Playa, montaña o ciudad: te decimos qué lugares van con tu estilo de viaje.'],
                        ['bi-calendar-week', 'Armar el itinerario', 'Organizamos los días para que aproveches el tiempo sin correr.'],
                        ['bi-compass', 'Escoger excursiones', 'Te recomendamos las mejores experiencias y agencias de cada zona.'],
                        ['bi-wallet2', 'Cuidar tu presupuesto', 'Opciones para cada bolsillo, con precios claros desde el principio.'],
                        ['bi-people', 'Familias y grupos', 'Planes pensados para niños, personas mayores o grupos grandes.'],
                        ['bi-car-front', 'Transporte', 'Te orientamos sobre cómo moverte: vehículo propio, chofer o traslados.'],
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
                <div class="accordion acordeon mt-3" id="preguntasAsesoria">
                    @foreach ([
                        '¿La asesoría cuesta algo?' => 'No. La asesoría de Explora RD es gratis. Solo pagas las excursiones o servicios que decidas contratar con las agencias.',
                        '¿Cuánto tardan en responder?' => 'Normalmente te contactamos el mismo día, y siempre en menos de 24 horas.',
                        '¿Puedo pedir ayuda si ya estoy en el país?' => 'Sí. Escríbenos por WhatsApp y te ayudamos a encontrar excursiones para los próximos días.',
                    ] as $pregunta => $respuesta)
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#preguntaA{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ __($pregunta) }}
                                </button>
                            </h3>
                            <div id="preguntaA{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#preguntasAsesoria">
                                <div class="accordion-body">{{ __($respuesta) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Formulario (se queda fijo al bajar en computadora) --}}
            <aside class="col-lg-5 col-xl-4">
                @include('servicios._formulario', ['servicio' => 'asesoria'])
            </aside>
        </div>
    </div>
</section>

@endsection
