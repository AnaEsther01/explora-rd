@extends('layouts.admin')

@section('titulo', 'Reporte — Panel del administrador | Explora RD')
@section('seccion', 'reporte')

@section('panel')
<div class="caja-panel mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h2 class="caja-lateral-titulo mb-1"><i class="bi bi-bar-chart-line"></i> Reporte de estadísticas</h2>
            <p class="small text-muted mb-0">
                Lo crea un programa de <strong>Python</strong> (<code>python/reporte.py</code>) con gráficas de <strong>matplotlib</strong>:
                reservas por mes, excursiones más reservadas y mejor valoradas, destinos con más interés y resumen de cada agencia.
            </p>
            @if ($existe)
                <p class="small mb-0 mt-2"><i class="bi bi-clock-history"></i> Última actualización: <strong>{{ $fecha->format('d/m/Y h:i A') }}</strong></p>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.reporte.generar') }}" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').innerHTML = '<span class=\'spinner-border spinner-border-sm\'></span> Creando…';">
                @csrf
                <button type="submit" class="btn btn-turquesa"><i class="bi bi-arrow-repeat"></i> {{ $existe ? 'Actualizar reporte' : 'Generar reporte' }}</button>
            </form>
            @if ($existe)
                <a href="{{ route('admin.reporte.ver') }}" target="_blank" rel="noopener" class="btn btn-contorno"><i class="bi bi-box-arrow-up-right"></i> Abrir para imprimir</a>
            @endif
        </div>
    </div>
</div>

@if ($existe)
    <iframe src="{{ route('admin.reporte.ver') }}" class="marco-reporte" title="Reporte de estadísticas de Explora RD"></iframe>
@else
    <div class="caja-panel">
        <div class="aviso-vacio">
            <i class="bi bi-bar-chart"></i>
            <p>Todavía no hay reporte. Pulsa «Generar reporte».<br>
               También se puede crear desde la terminal con <code>php artisan explora:reporte</code>.</p>
        </div>
    </div>
@endif
@endsection
