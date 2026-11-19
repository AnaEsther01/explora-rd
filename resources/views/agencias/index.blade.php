@extends('layouts.app')

@section('titulo', __('Agencias turísticas de República Dominicana').' — Explora RD')
@section('descripcion', __('Conoce las agencias turísticas locales de Explora RD y contacta directamente por WhatsApp.'))

@section('contenido')

@include('partials.cabecera', [
    'foto' => 'img/destinos/la-romana.jpg',
    'etiqueta' => e(__('Agencias')),
    'titulo' => __('Agencias turísticas locales'),
    'subtitulo' => __('Conoce a quienes organizan las experiencias y contáctalos directamente, sin intermediarios.'),
    'migas' => [__('Inicio') => route('inicio'), __('Agencias') => null],
])

<section class="seccion seccion-clara pt-5">
    <div class="container">
        <div class="row g-4">
            @forelse ($agencias as $agencia)
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="{{ route('agencias.show', $agencia) }}" class="tarjeta-agencia">
                        <div class="tarjeta-agencia-portada">
                            @php $portada = $agencia->fotosExistentes()[0] ?? null; @endphp
                            @include('partials.foto', ['ruta' => $portada, 'tipo' => 'playa', 'alt' => ''])
                        </div>
                        <div class="tarjeta-agencia-cuerpo">
                            @if ($agencia->tieneLogo())
                                <img src="{{ asset($agencia->logo) }}" alt="{{ __('Logo de :nombre', ['nombre' => $agencia->nombreVisible()]) }}" class="tarjeta-agencia-logo">
                            @else
                                <span class="tarjeta-agencia-logo agencia-iniciales">{{ $agencia->iniciales() }}</span>
                            @endif
                            <h2>{{ $agencia->nombreVisible() }}</h2>
                            @if ($agencia->direccion)
                                <p class="small text-muted mb-2"><i class="bi bi-geo-alt"></i> {{ $agencia->direccion }}</p>
                            @endif
                            <p class="tarjeta-agencia-texto">{{ \Illuminate\Support\Str::limit($agencia->descripcion, 130) }}</p>
                            <p class="tarjeta-contador mb-0">
                                <i class="bi bi-compass"></i> {{ trans_choice(':count experiencia|:count experiencias', $agencia->excursiones_count) }}
                                <i class="bi bi-arrow-right ms-auto"></i>
                            </p>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="aviso-vacio"><i class="bi bi-building"></i><p>{{ __('Todavía no hay agencias registradas.') }}</p></div></div>
            @endforelse
        </div>

        <div class="banda-unete mt-5">
            <div>
                <h2>{{ __('¿Tienes una agencia de turismo?') }}</h2>
                <p class="mb-0">{{ __('Crea tu perfil gratis, publica tus excursiones y recibe clientes por WhatsApp.') }}</p>
            </div>
            <a href="{{ route('registro', ['tipo' => 'agencia']) }}" class="btn btn-arena btn-lg">{{ __('Únete como agencia') }}</a>
        </div>
    </div>
</section>

@endsection
