@extends('layouts.admin')

@section('titulo', 'Asesorías y vehículos — Panel del administrador | Explora RD')
@section('seccion', 'solicitudes')

@section('panel')
<div class="caja-panel">
    <h2 class="caja-lateral-titulo">Solicitudes de asesoría y renta de vehículos</h2>
    <p class="small text-muted">Los turistas las envían desde las páginas de Asesorías y Renta de vehículos. Contáctalos, marca la solicitud como «Atendida» y déjales una respuesta: la verán en «Mi cuenta».</p>

    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-sm-4">
            <label for="servicio" class="form-label">Servicio</label>
            <select id="servicio" name="servicio" class="form-select" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach (\App\Models\Solicitud::SERVICIOS as $clave => $s)
                    <option value="{{ $clave }}" @selected($servicio === $clave)>{{ $s['nombre'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-4">
            <label for="estado" class="form-label">Estado</label>
            <select id="estado" name="estado" class="form-select" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach (\App\Models\Solicitud::ESTADOS as $e)
                    <option value="{{ $e }}" @selected($estado === $e)>{{ ucfirst($e) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-4">
            <a href="{{ route('admin.solicitudes.index') }}" class="btn btn-contorno w-100">Quitar filtros</a>
        </div>
    </form>

    @forelse ($solicitudes as $solicitud)
        @php $turista = $solicitud->usuario; @endphp
        <div class="fila-reserva {{ $solicitud->estado === 'cancelada' ? 'fila-reserva-apagada' : '' }}">
            <span class="icono-solicitud"><i class="bi {{ $solicitud->iconoServicio() }}"></i></span>
            <div class="flex-grow-1">
                <span class="fila-reserva-titulo">#{{ $solicitud->id }} · {{ \App\Models\Solicitud::SERVICIOS[$solicitud->servicio]['nombre'] }}</span>
                <p class="fila-reserva-datos">
                    <span><i class="bi bi-person"></i> {{ $turista->nombre }}</span>
                    <span><i class="bi bi-telephone"></i> {{ $solicitud->telefono }}</span>
                    <span class="text-break"><i class="bi bi-envelope"></i> {{ $turista->correo }}</span>
                </p>
                <p class="fila-reserva-datos">
                    <span><i class="bi bi-geo-alt"></i> {{ $solicitud->lugar }}</span>
                    <span><i class="bi bi-calendar-event"></i> {{ $solicitud->fechasTexto() }}</span>
                    <span><i class="bi bi-people"></i> {{ $solicitud->cantidad_personas }} {{ $solicitud->cantidad_personas === 1 ? 'persona' : 'personas' }}</span>
                    @if ($solicitud->tipo_vehiculo)
                        <span><i class="bi bi-car-front"></i> {{ \App\Models\Solicitud::TIPOS_VEHICULO[$solicitud->tipo_vehiculo]['nombre'] ?? $solicitud->tipo_vehiculo }}</span>
                    @endif
                    <span class="text-muted"><i class="bi bi-clock"></i> Enviada el {{ $solicitud->created_at->format('d/m/Y H:i') }}</span>
                </p>
                @if ($solicitud->mensaje)
                    <p class="small mt-2 mb-0"><i class="bi bi-chat-left-quote"></i> «{{ $solicitud->mensaje }}»</p>
                @endif

                {{-- Atender: estado y respuesta para el turista --}}
                <form method="POST" action="{{ route('admin.solicitudes.update', $solicitud) }}" class="row g-2 align-items-end mt-2">
                    @csrf
                    @method('PATCH')
                    <div class="col-sm-3">
                        <label class="form-label small mb-1" for="estado{{ $solicitud->id }}">Estado</label>
                        <select id="estado{{ $solicitud->id }}" name="estado" class="form-select form-select-sm">
                            @foreach (\App\Models\Solicitud::ESTADOS as $e)
                                <option value="{{ $e }}" @selected($solicitud->estado === $e)>{{ ucfirst($e) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-5">
                        <label class="form-label small mb-1" for="respuesta{{ $solicitud->id }}">Respuesta para el turista</label>
                        <input type="text" id="respuesta{{ $solicitud->id }}" name="respuesta" maxlength="1000"
                               class="form-control form-control-sm" value="{{ $solicitud->respuesta }}" placeholder="Ej.: Te escribimos por WhatsApp con las opciones.">
                    </div>
                    <div class="col-sm-4 d-flex gap-2">
                        <button type="submit" class="btn btn-turquesa btn-sm flex-grow-1 text-nowrap"><i class="bi bi-check2"></i> Guardar</button>
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $solicitud->telefono) }}" target="_blank" rel="noopener"
                           class="btn btn-sm btn-whatsapp-ancho" title="Escribir por WhatsApp"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </form>
            </div>
            @include('panel._estado', ['estado' => $solicitud->estado])
        </div>
    @empty
        <div class="aviso-vacio"><i class="bi bi-chat-heart"></i><p>No hay solicitudes con esos filtros.</p></div>
    @endforelse

    <div class="mt-4 d-flex justify-content-center">{{ $solicitudes->links() }}</div>
</div>
@endsection
