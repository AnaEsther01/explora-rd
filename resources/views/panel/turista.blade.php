@extends('layouts.app')

@section('titulo', __('Mi cuenta').' — Explora RD')

@section('contenido')

@include('panel._cabecera', [
    'titulo' => __('Hola, :nombre', ['nombre' => $usuario->nombre]),
    'subtitulo' => __('Tu cuenta de viajero en Explora RD'),
    'icono' => 'bi-person-walking',
])

<section class="seccion seccion-clara pt-5">
    <div class="container">
        <div class="row g-4">
            <aside class="col-lg-4">
                <div class="caja-lateral">
                    <h2 class="caja-lateral-titulo">{{ __('Mis datos') }}</h2>
                    <ul class="datos-lista">
                        <li><i class="bi bi-person"></i> <span>{{ __('Nombre') }}</span> <strong>{{ $usuario->nombre }}</strong></li>
                        <li><i class="bi bi-envelope"></i> <span>{{ __('Correo') }}</span> <strong class="text-break">{{ $usuario->correo }}</strong></li>
                        <li><i class="bi bi-telephone"></i> <span>{{ __('Teléfono') }}</span> <strong>{{ $usuario->telefono ?: '—' }}</strong></li>
                        <li><i class="bi bi-calendar3"></i> <span>{{ __('Miembro desde') }}</span> <strong>{{ $usuario->created_at?->format('d/m/Y') }}</strong></li>
                    </ul>
                    <a href="{{ route('excursiones.index') }}" class="btn btn-turquesa w-100"><i class="bi bi-compass"></i> {{ __('Buscar excursiones') }}</a>
                </div>
            </aside>

            <div class="col-lg-8">
                <div class="caja-panel">
                    <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2">
                        <h2 class="caja-lateral-titulo">{{ __('Mis reservas') }}</h2>
                        @if ($reservas->isNotEmpty())
                            <small class="text-muted">
                                {{ trans_choice(':count pendiente|:count pendientes', $reservas->where('estado', 'pendiente')->count()) }} ·
                                {{ trans_choice(':count confirmada|:count confirmadas', $reservas->where('estado', 'confirmada')->count()) }}
                            </small>
                        @endif
                    </div>

                    @forelse ($reservas as $reserva)
                        @php $excursion = $reserva->excursion; @endphp
                        <div class="fila-reserva {{ $reserva->estado === 'cancelada' || $reserva->yaPaso() ? 'fila-reserva-apagada' : '' }}">
                            <div class="fila-reserva-foto">
                                @include('partials.foto', ['ruta' => $excursion->foto, 'tipo' => $excursion->destino->tipo, 'alt' => $excursion->titulo])
                            </div>
                            <div class="flex-grow-1">
                                <a href="{{ route('excursiones.show', $excursion) }}" class="fila-reserva-titulo">{{ $excursion->titulo }}</a>
                                <p class="fila-reserva-datos">
                                    <span><i class="bi bi-calendar-event"></i> {{ $reserva->fecha->format('d/m/Y') }}@if ($reserva->yaPaso()) ({{ __('ya pasó') }})@endif</span>
                                    <span><i class="bi bi-people"></i> {{ trans_choice(':count persona|:count personas', $reserva->cantidad_personas) }}</span>
                                    <span><i class="bi bi-cash-coin"></i> {{ $reserva->totalFormateado() }}</span>
                                    <span><i class="bi bi-building"></i> {{ $excursion->agencia->nombreVisible() }}</span>
                                </p>
                                @if ($reserva->estado === 'confirmada' && ! $reserva->yaPaso())
                                    <p class="fila-reserva-nota"><i class="bi bi-check2-circle"></i> {{ __('¡Confirmada! La agencia te contactará con la hora y el punto de encuentro.') }}</p>
                                @elseif ($reserva->estaPendiente())
                                    <p class="fila-reserva-nota"><i class="bi bi-hourglass-split"></i> {{ __('Esperando la respuesta de la agencia.') }}</p>
                                @endif
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    {{-- Ya hizo la excursión: puede contar su experiencia --}}
                                    @if ($reserva->estado === 'confirmada' && $reserva->yaPaso())
                                        @if (in_array($excursion->id, $resenadas))
                                            <a href="{{ route('excursiones.show', $excursion) }}#resenas" class="btn btn-sm btn-contorno"><i class="bi bi-star"></i> {{ __('Ver mi reseña') }}</a>
                                        @else
                                            <a href="{{ route('excursiones.show', $excursion) }}#resenas" class="btn btn-sm btn-turquesa"><i class="bi bi-star-fill"></i> {{ __('Escribir reseña') }}</a>
                                        @endif
                                    @endif
                                    <a href="{{ $excursion->enlaceWhatsapp($reserva->fecha->format('d/m/Y'), $reserva->cantidad_personas) }}"
                                       class="btn btn-sm btn-whatsapp-ancho" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> {{ __('Escribir a la agencia') }}</a>
                                    @if ($reserva->puedeCancelarla())
                                        <form method="POST" action="{{ route('reservas.cancelar', $reserva) }}"
                                              onsubmit="return confirm('{{ addslashes(__('¿Cancelar tu reserva de «:titulo»?', ['titulo' => $excursion->titulo])) }}')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-contorno-peligro"><i class="bi bi-x-circle"></i> {{ __('Cancelar reserva') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            @include('panel._estado', ['estado' => $reserva->estado])
                        </div>
                    @empty
                        <div class="aviso-vacio">
                            <i class="bi bi-calendar-heart"></i>
                            <p>{{ __('Todavía no tienes reservas.') }}<br>{{ __('Elige una excursión y pulsa «Solicitar reserva».') }}</p>
                            <a href="{{ route('excursiones.index') }}" class="btn btn-turquesa">{{ __('Ver excursiones') }}</a>
                        </div>
                    @endforelse
                </div>

                {{-- Excursiones guardadas con el corazón ♥ --}}
                <div class="caja-panel mt-4" id="favoritos">
                    <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2 mb-3">
                        <h2 class="caja-lateral-titulo mb-0"><i class="bi bi-heart-fill text-danger"></i> {{ __('Mis favoritos') }}</h2>
                        @if ($favoritas->isNotEmpty())
                            <small class="text-muted">{{ trans_choice(':count excursión guardada|:count excursiones guardadas', $favoritas->count()) }}</small>
                        @endif
                    </div>

                    @if ($favoritas->isNotEmpty())
                        <div class="row g-3">
                            @foreach ($favoritas as $excursion)
                                <div class="col-md-6">
                                    @include('partials.tarjeta-excursion', ['excursion' => $excursion])
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="aviso-vacio">
                            <i class="bi bi-heart"></i>
                            <p>{{ __('Todavía no has guardado excursiones.') }}<br>{{ __('Toca el corazón de una excursión para guardarla aquí.') }}</p>
                            <a href="{{ route('excursiones.index') }}" class="btn btn-turquesa">{{ __('Ver excursiones') }}</a>
                        </div>
                    @endif
                </div>

                {{-- Solicitudes de asesoría y de renta de vehículos --}}
                <div class="caja-panel mt-4" id="solicitudes">
                    <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2">
                        <h2 class="caja-lateral-titulo">{{ __('Mis solicitudes') }}</h2>
                        <small class="text-muted">{{ __('Asesorías y renta de vehículos') }}</small>
                    </div>

                    @forelse ($solicitudes as $solicitud)
                        <div class="fila-reserva {{ $solicitud->estado === 'cancelada' ? 'fila-reserva-apagada' : '' }}">
                            <span class="icono-solicitud"><i class="bi {{ $solicitud->iconoServicio() }}"></i></span>
                            <div class="flex-grow-1">
                                <span class="fila-reserva-titulo">{{ $solicitud->nombreServicio() }}</span>
                                <p class="fila-reserva-datos">
                                    <span><i class="bi bi-geo-alt"></i> {{ $solicitud->lugar }}</span>
                                    <span><i class="bi bi-calendar-event"></i> {{ $solicitud->fechasTexto() }}</span>
                                    <span><i class="bi bi-people"></i> {{ trans_choice(':count persona|:count personas', $solicitud->cantidad_personas) }}</span>
                                    @if ($solicitud->nombreVehiculo())
                                        <span><i class="bi bi-car-front"></i> {{ $solicitud->nombreVehiculo() }}</span>
                                    @endif
                                </p>
                                @if ($solicitud->respuesta)
                                    <p class="respuesta-solicitud"><strong>{{ __('Respuesta de Explora RD:') }}</strong> {{ $solicitud->respuesta }}</p>
                                @elseif ($solicitud->estaPendiente())
                                    <p class="fila-reserva-nota"><i class="bi bi-hourglass-split"></i> {{ __('Te contactaremos pronto al :telefono.', ['telefono' => $solicitud->telefono]) }}</p>
                                @endif
                                @if ($solicitud->puedeCancelarla())
                                    <form method="POST" action="{{ route('solicitudes.cancelar', $solicitud) }}" class="mt-2"
                                          onsubmit="return confirm('{{ addslashes(__('¿Cancelar esta solicitud?')) }}')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-contorno-peligro"><i class="bi bi-x-circle"></i> {{ __('Cancelar solicitud') }}</button>
                                    </form>
                                @endif
                            </div>
                            @include('panel._estado', ['estado' => $solicitud->estado])
                        </div>
                    @empty
                        <div class="aviso-vacio">
                            <i class="bi bi-chat-heart"></i>
                            <p>{{ __('¿Necesitas ayuda con tu viaje o un vehículo?') }}</p>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="{{ route('servicios.asesorias') }}" class="btn btn-turquesa">{{ __('Pedir asesoría') }}</a>
                                <a href="{{ route('servicios.vehiculos') }}" class="btn btn-contorno">{{ __('Renta de vehículos') }}</a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
