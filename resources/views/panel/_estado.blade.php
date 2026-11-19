{{-- Etiqueta de color para el estado de una reserva. Variable: $estado --}}
@php
    $estados = [
        'pendiente' => ['Pendiente', 'estado-pendiente', 'bi-hourglass-split'],
        'confirmada' => ['Confirmada', 'estado-confirmada', 'bi-check-circle'],
        'cancelada' => ['Cancelada', 'estado-cancelada', 'bi-x-circle'],
        'atendida' => ['Atendida', 'estado-confirmada', 'bi-check2-all'], // solicitudes de asesoría o vehículo
    ];
    [$texto, $clase, $icono] = $estados[$estado] ?? [ucfirst($estado), 'estado-pendiente', 'bi-circle'];
@endphp
<span class="estado {{ $clase }}"><i class="bi {{ $icono }}"></i> {{ __($texto) }}</span>
