{{-- Botones Confirmar / Rechazar / Ver detalle de una reserva (boceto 19). Variable: $reserva --}}
<div class="d-flex gap-2 justify-content-end acciones-reserva">
    @if ($reserva->estaPendiente())
        <form method="POST" action="{{ route('agencia.reservas.confirmar', $reserva) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm btn-confirmar"><i class="bi bi-check-lg"></i> Confirmar</button>
        </form>
        <form method="POST" action="{{ route('agencia.reservas.rechazar', $reserva) }}"
              onsubmit="return confirm('¿Rechazar la reserva de {{ addslashes($reserva->usuario->nombre) }}? Se le avisará por correo.')">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm btn-contorno-peligro"><i class="bi bi-x-lg"></i> Rechazar</button>
        </form>
    @endif
    @if (empty($sinDetalle))
        <a href="{{ route('agencia.reservas.show', $reserva) }}" class="btn btn-sm btn-contorno">Ver detalle</a>
    @endif
</div>
