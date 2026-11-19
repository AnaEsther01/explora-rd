{{--
    Chat «Pregúntale a Explora RD»: un asistente con IA para las preguntas abiertas de los turistas.
    El botón va encima del de WhatsApp. public/js/asistente.js envía las preguntas a POST /asistente.
    No sale en los paneles de agencia ni de administrador.
    Mientras no haya clave de la IA (ANTHROPIC_API_KEY en .env), sale en modo «Próximamente»:
    el botón lo dice y la ventana solo ofrece WhatsApp. Con la clave, se vuelve el chat de verdad.
--}}
@inject('asistenteIA', 'App\Contracts\AsistenteTuristico')
@php
    $proximamente = ! $asistenteIA->disponible();
    $sugerencias = [
        __('¿Qué puedo hacer en Samaná con niños?'),
        __('Recomiéndame una excursión de aventura barata'),
        __('¿Qué destino me recomiendas para 3 días?'),
    ];
@endphp

<button type="button" class="asistente-boton {{ $proximamente ? 'asistente-proximamente' : '' }}" id="asistenteBoton" aria-controls="asistentePanel" aria-expanded="false">
    <i class="bi bi-stars"></i>
    @if ($proximamente)
        <span>{{ __('Asistente IA') }} <small class="etiqueta-pronto">{{ __('Próximamente') }}</small></span>
    @else
        <span>{{ __('Pregúntale a Explora RD') }}</span>
    @endif
</button>

<section class="asistente-panel" id="asistentePanel" role="dialog" aria-modal="false" aria-labelledby="asistenteTitulo" hidden
         data-url="{{ route('asistente') }}"
         data-whatsapp="https://wa.me/{{ config('explora.whatsapp') }}"
         data-texto-error="{{ __('No pude conectarme. Revisa tu internet e inténtalo otra vez.') }}"
         data-texto-esperando="{{ __('Pensando…') }}"
         data-texto-muchas="{{ __('Hiciste muchas preguntas seguidas. Espera un minuto, por favor.') }}">
    <header class="asistente-cabecera">
        <span class="asistente-avatar"><i class="bi bi-stars"></i></span>
        <div>
            <h2 id="asistenteTitulo">{{ __('Asistente de Explora RD') }}</h2>
            <small>{{ __('Con inteligencia artificial') }}</small>
        </div>
        @unless ($proximamente)
            <button type="button" class="asistente-icono" id="asistenteLimpiar" title="{{ __('Empezar de nuevo') }}" aria-label="{{ __('Empezar de nuevo') }}"><i class="bi bi-arrow-counterclockwise"></i></button>
        @endunless
        <button type="button" class="asistente-icono" id="asistenteCerrar" title="{{ __('Cerrar') }}" aria-label="{{ __('Cerrar') }}"><i class="bi bi-x-lg"></i></button>
    </header>

    @if ($proximamente)
        {{-- Modo «Próximamente»: sin caja para escribir; solo el aviso y WhatsApp --}}
        <div class="asistente-pronto">
            <span class="asistente-pronto-icono"><i class="bi bi-stars"></i></span>
            <p class="etiqueta-pronto">{{ __('Próximamente') }}</p>
            <h3>{{ __('Muy pronto podrás preguntarle a nuestra IA') }}</h3>
            <p>{{ __('Estamos preparando un asistente con inteligencia artificial que te ayudará a elegir destinos y excursiones y a organizar tu viaje.') }}</p>
            <p>{{ __('Mientras tanto, escríbenos por WhatsApp y te ayudamos con gusto.') }}</p>
            <a href="https://wa.me/{{ config('explora.whatsapp') }}?text={{ rawurlencode(__('¡Hola, Explora RD! Quisiera ayuda para organizar mi viaje.')) }}"
               class="btn btn-whatsapp-ancho w-100" target="_blank" rel="noopener">
                <i class="bi bi-whatsapp"></i> {{ __('Escríbenos por WhatsApp') }}
            </a>
        </div>
    @else
    <div class="asistente-mensajes" id="asistenteMensajes" aria-live="polite">
        <div class="asistente-mensaje asistente-ia">{{ __('¡Hola! Soy el asistente de Explora RD. Pregúntame lo que quieras sobre destinos, excursiones o cómo organizar tu viaje por República Dominicana.') }}</div>
        <div class="asistente-sugerencias" id="asistenteSugerencias">
            @foreach ($sugerencias as $sugerencia)
                <button type="button" class="asistente-sugerencia">{{ $sugerencia }}</button>
            @endforeach
        </div>
    </div>

    <form class="asistente-formulario" id="asistenteFormulario">
        <label for="asistentePregunta" class="visually-hidden">{{ __('Escribe tu pregunta') }}</label>
        <textarea id="asistentePregunta" rows="1" maxlength="1000" placeholder="{{ __('Escribe tu pregunta…') }}" required></textarea>
        <button type="submit" class="asistente-enviar" aria-label="{{ __('Enviar') }}"><i class="bi bi-send-fill"></i></button>
    </form>
    <p class="asistente-aviso">{{ __('Las respuestas las genera una IA y pueden tener errores. Confirma precios y horarios con la agencia.') }}</p>
    @endif
</section>

@push('scripts')
    <script src="{{ asset('js/asistente.js') }}"></script>
@endpush
