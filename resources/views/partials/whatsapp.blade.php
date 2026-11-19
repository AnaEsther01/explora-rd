{{-- Botón flotante de WhatsApp (siempre visible abajo a la derecha) --}}
<a href="https://wa.me/{{ config('explora.whatsapp') }}?text={{ rawurlencode(__('¡Hola, Explora RD! Quisiera información sobre excursiones.')) }}"
   class="whatsapp-flotante" target="_blank" rel="noopener" aria-label="{{ __('Escríbenos por WhatsApp') }}">
    <i class="bi bi-whatsapp"></i> <span>WhatsApp</span>
</a>
