@extends('layouts.agencia')

@php
    $cliente = $reserva->usuario;
    $excursion = $reserva->excursion;
    // WhatsApp del turista: su teléfono solo con números (si escribió 10 dígitos de RD, se agrega el 1)
    $telefonoCliente = preg_replace('/\D/', '', (string) $cliente->telefono);
    if (strlen($telefonoCliente) === 10) {
        $telefonoCliente = '1'.$telefonoCliente;
    }
    $mensajeCliente = '¡Hola, '.$cliente->nombre.'! Le escribimos de '.auth()->user()->nombreVisible()
        .' por su reserva de «'.$excursion->titulo.'» para el '.$reserva->fecha->format('d/m/Y').'.';
@endphp

@section('titulo', 'Reserva de '.$cliente->nombre.' — Panel de la agencia | Explora RD')
@section('seccion', 'reservas')

@section('panel')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h2 class="subtitulo-lora mb-0">Reserva #{{ $reserva->id }}</h2>
    <a href="{{ route('agencia.reservas.index') }}" class="enlace-suave"><i class="bi bi-arrow-left"></i> Volver a las reservas</a>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="caja-panel h-100">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                <h3 class="caja-lateral-titulo mb-0">Detalle</h3>
                @include('panel._estado', ['estado' => $reserva->estado])
            </div>
            <ul class="datos-lista">
                <li><i class="bi bi-compass"></i> <span>Excursión</span> <strong>{{ $excursion->titulo }}</strong></li>
                <li><i class="bi bi-geo-alt"></i> <span>Destino</span> <strong>{{ $excursion->destino->nombre }}</strong></li>
                <li><i class="bi bi-calendar-event"></i> <span>Fecha</span> <strong>{{ $reserva->fecha->translatedFormat('l j \d\e F \d\e Y') }}</strong></li>
                <li><i class="bi bi-people"></i> <span>Personas</span> <strong>{{ $reserva->cantidad_personas }}</strong></li>
                <li><i class="bi bi-cash-coin"></i> <span>Total estimado</span> <strong>{{ $reserva->totalFormateado() }}</strong></li>
                <li><i class="bi bi-clock-history"></i> <span>Solicitada</span> <strong>{{ $reserva->created_at?->format('d/m/Y h:i a') }}</strong></li>
            </ul>
            @if ($reserva->comentario)
                <div class="comentario-reserva">
                    <i class="bi bi-chat-quote"></i>
                    <p class="mb-0">{{ $reserva->comentario }}</p>
                </div>
            @endif

            @if ($reserva->estaPendiente())
                <div class="mt-4">
                    <p class="small text-muted mb-2">¿Tienes disponibilidad para esta fecha?</p>
                    @include('agencia.reservas._acciones', ['reserva' => $reserva, 'sinDetalle' => true])
                </div>
            @endif
        </div>
    </div>

    <div class="col-xl-5">
        <div class="caja-panel h-100">
            <h3 class="caja-lateral-titulo">Cliente</h3>
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="agencia-logo agencia-iniciales">{{ $cliente->iniciales() }}</span>
                <div>
                    <strong class="d-block">{{ $cliente->nombre }}</strong>
                    <small class="text-muted">Turista desde {{ $cliente->created_at?->format('m/Y') }}</small>
                </div>
            </div>
            <ul class="datos-agencia list-unstyled mb-3">
                <li><i class="bi bi-envelope"></i> <a href="mailto:{{ $cliente->correo }}">{{ $cliente->correo }}</a></li>
                @if ($cliente->telefono)
                    <li><i class="bi bi-telephone"></i> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $cliente->telefono) }}">{{ $cliente->telefono }}</a></li>
                @endif
            </ul>
            @if (strlen($telefonoCliente) >= 10)
                <a href="https://wa.me/{{ $telefonoCliente }}?text={{ rawurlencode($mensajeCliente) }}" class="btn btn-whatsapp-ancho w-100" target="_blank" rel="noopener">
                    <i class="bi bi-whatsapp"></i> Escribir al cliente
                </a>
            @else
                <a href="mailto:{{ $cliente->correo }}?subject={{ rawurlencode('Su reserva: '.$excursion->titulo) }}" class="btn btn-contorno w-100">
                    <i class="bi bi-envelope"></i> Escribir por correo
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
