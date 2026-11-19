{{--
    Formulario de solicitud que comparten las páginas de Asesorías y Renta de vehículos.
    Variables:
      $servicio  'asesoria' o 'vehiculo'
      $previo    ['lugar', 'fecha', 'personas'] que vienen del buscador de la portada
      $lugares   lista de sugerencias para el campo «lugar»
--}}
@php
    $usuario = auth()->user();
    $puedePedir = $usuario?->esTurista();
    $esVehiculo = $servicio === 'vehiculo';
    $manana = now()->addDay()->format('Y-m-d');
    $personasElegidas = (int) old('cantidad_personas', $previo['personas']);
    $mensajeWhatsapp = $esVehiculo
        ? __('Hola, quisiera información sobre renta de vehículos.')
        : __('Hola, quisiera una asesoría para organizar mi viaje.');
@endphp

<div class="caja-reserva caja-servicio" id="solicitar">
    <div class="caja-reserva-precio">
        <small>{{ __('Solicitud gratuita') }}</small>
        <strong class="caja-servicio-titulo">{{ $esVehiculo ? __('Pide tu vehículo') : __('Pide tu asesoría') }}</strong>
        <small>{{ __('Te respondemos en menos de 24 horas') }}</small>
    </div>

    <form class="caja-reserva-form" method="POST" action="{{ route('solicitudes.store') }}" novalidate>
        @csrf
        <input type="hidden" name="servicio" value="{{ $servicio }}">

        <div class="mb-3">
            <label for="campoLugar" class="form-label">{{ $esVehiculo ? __('Lugar de recogida') : __('Destino de interés') }}</label>
            <input type="text" class="form-control @error('lugar') is-invalid @enderror" id="campoLugar" name="lugar"
                   list="listaLugares" maxlength="120" required value="{{ old('lugar', $previo['lugar']) }}"
                   placeholder="{{ $esVehiculo ? __('Aeropuerto, ciudad u hotel') : __('¿Qué te gustaría conocer?') }}">
            <datalist id="listaLugares">
                @foreach ($lugares as $lugar)
                    <option value="{{ $lugar }}">
                @endforeach
            </datalist>
            @error('lugar') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6">
                <label for="campoInicio" class="form-label">{{ $esVehiculo ? __('Recogida') : __('Llegada') }}</label>
                <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" id="campoInicio" name="fecha_inicio"
                       min="{{ $manana }}" required value="{{ old('fecha_inicio', $previo['fecha']) }}">
                @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-6">
                <label for="campoFin" class="form-label">
                    {{ $esVehiculo ? __('Devolución') : __('Salida') }}
                    @unless ($esVehiculo) <span class="opcional">({{ __('opcional') }})</span> @endunless
                </label>
                <input type="date" class="form-control @error('fecha_fin') is-invalid @enderror" id="campoFin" name="fecha_fin"
                       min="{{ $manana }}" @if ($esVehiculo) required @endif value="{{ old('fecha_fin') }}">
                @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="campoPersonasServicio" class="form-label">{{ $esVehiculo ? __('Pasajeros') : __('Viajeros') }}</label>
            <select class="form-select @error('cantidad_personas') is-invalid @enderror" id="campoPersonasServicio" name="cantidad_personas">
                @for ($i = 1; $i <= 15; $i++)
                    <option value="{{ $i }}" @selected($personasElegidas === $i)>{{ trans_choice(':count persona|:count personas', $i) }}</option>
                @endfor
            </select>
            @error('cantidad_personas') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        @if ($esVehiculo)
            <div class="mb-3">
                <label for="campoVehiculo" class="form-label">{{ __('Tipo de vehículo') }}</label>
                <select class="form-select @error('tipo_vehiculo') is-invalid @enderror" id="campoVehiculo" name="tipo_vehiculo" required>
                    <option value="">{{ __('Elige uno') }}</option>
                    @foreach (\App\Models\Solicitud::TIPOS_VEHICULO as $clave => $tipo)
                        <option value="{{ $clave }}" @selected(old('tipo_vehiculo', request('vehiculo')) === $clave)>{{ __($tipo['nombre']) }}</option>
                    @endforeach
                </select>
                @error('tipo_vehiculo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        @endif

        @if ($puedePedir)
            <div class="mb-3">
                <label for="campoTelefono" class="form-label">{{ __('Teléfono o WhatsApp') }}</label>
                <input type="tel" class="form-control @error('telefono') is-invalid @enderror" id="campoTelefono" name="telefono"
                       maxlength="30" required value="{{ old('telefono', $usuario->telefono) }}" placeholder="809-000-0000">
                @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="campoMensaje" class="form-label">{{ __('Mensaje') }} <span class="opcional">({{ __('opcional') }})</span></label>
                <textarea class="form-control @error('mensaje') is-invalid @enderror" id="campoMensaje" name="mensaje" rows="3" maxlength="1000"
                          placeholder="{{ $esVehiculo ? __('Ej.: necesito asiento para niño, transmisión automática...') : __('Ej.: viajamos en familia, nos gusta la playa y la comida típica...') }}">{{ old('mensaje') }}</textarea>
                @error('mensaje') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-turquesa w-100 btn-auth">
                <i class="bi bi-send"></i> {{ __('Enviar solicitud') }}
            </button>
            <p class="small text-muted text-center mt-2 mb-3">{{ __('No pagas nada ahora. Verás la respuesta en «Mi cuenta».') }}</p>
        @elseif (! $usuario)
            <a href="{{ route('login', ['volver' => request()->getRequestUri()]) }}" class="btn btn-turquesa w-100 btn-auth">
                <i class="bi bi-box-arrow-in-right"></i> {{ __('Inicia sesión para enviar') }}
            </a>
            <p class="small text-muted text-center mt-2 mb-3">{{ __('¿No tienes cuenta?') }} <a href="{{ route('registro') }}">{{ __('Regístrate gratis') }}</a></p>
        @else
            <p class="aviso-reserva"><i class="bi bi-info-circle"></i> {{ __('Las solicitudes las hacen las cuentas de turista.') }}</p>
        @endif

        <a href="https://wa.me/{{ config('explora.whatsapp') }}?text={{ rawurlencode($mensajeWhatsapp) }}"
           class="btn btn-whatsapp-ancho w-100" target="_blank" rel="noopener">
            <i class="bi bi-whatsapp"></i> {{ __('Escríbenos por WhatsApp') }}
        </a>
    </form>
</div>
