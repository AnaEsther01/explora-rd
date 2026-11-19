{{-- Mensajes de éxito o de error que se muestran después de una acción (flotan arriba a la derecha) --}}
@if (session('exito') || session('error'))
    <div class="alertas-flotantes" role="status" aria-live="polite">
        @if (session('exito'))
            <div class="alert alerta alerta-exito alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('exito') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Cerrar') }}"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alerta alerta-error alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Cerrar') }}"></button>
            </div>
        @endif
    </div>
@endif
