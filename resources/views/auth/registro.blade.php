@extends('layouts.auth')

@section('titulo', __('Crear cuenta').' — Explora RD')
@section('lado_titulo', __('Únete a Explora RD'))
@section('lado_texto', __('Crea tu cuenta para reservar excursiones, o registra tu agencia y promociona tus experiencias en toda República Dominicana.'))

@php
    // Si el formulario volvió con errores, se queda en la pestaña que eligió
    $tipo = old('rol', $tipo);
@endphp

@section('contenido')
    <h2 class="auth-titulo">{{ __('Crear cuenta') }}</h2>
    <p class="auth-subtitulo">{{ __('¿Cómo vas a usar Explora RD?') }}</p>

    {{-- Pestañas: Turista / Agencia --}}
    <div class="selector-rol" role="radiogroup" aria-label="{{ __('Tipo de cuenta') }}">
        <label class="opcion-rol">
            <input type="radio" name="rol_visual" value="turista" @checked($tipo === 'turista')>
            <span><i class="bi bi-person-walking"></i> {{ __('Soy turista') }} <small>{{ __('Quiero descubrir y reservar') }}</small></span>
        </label>
        <label class="opcion-rol">
            <input type="radio" name="rol_visual" value="agencia" @checked($tipo === 'agencia')>
            <span><i class="bi bi-building"></i> {{ __('Soy agencia') }} <small>{{ __('Quiero publicar excursiones') }}</small></span>
        </label>
    </div>

    <form method="POST" action="{{ route('registro.store') }}" id="formRegistro" novalidate>
        @csrf
        <input type="hidden" name="rol" id="campoRol" value="{{ $tipo }}">

        {{-- Datos de la agencia (solo se ven si elige «Soy agencia») --}}
        <div id="camposAgencia" class="campos-agencia {{ $tipo === 'agencia' ? '' : 'd-none' }}">
            <div class="mb-3">
                <label for="nombre_comercial" class="form-label">{{ __('Nombre de la agencia') }}</label>
                <input type="text" id="nombre_comercial" name="nombre_comercial" value="{{ old('nombre_comercial') }}"
                       class="form-control @error('nombre_comercial') is-invalid @enderror" placeholder="{{ __('Ej.: Aventuras Caribe') }}" maxlength="150">
                @error('nombre_comercial') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="nombre" class="form-label" id="etiquetaNombre">{{ $tipo === 'agencia' ? __('Nombre del responsable') : __('Nombre completo') }}</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}"
                   class="form-control @error('nombre') is-invalid @enderror" autocomplete="name" maxlength="100" required>
            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="correo" class="form-label">{{ __('Correo electrónico') }}</label>
            <input type="email" id="correo" name="correo" value="{{ old('correo') }}"
                   class="form-control @error('correo') is-invalid @enderror" placeholder="ana@correo.com" autocomplete="email" maxlength="100" required>
            @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-6">
                <label for="telefono" class="form-label">{{ __('Teléfono') }} <span class="opcional">({{ __('opcional') }})</span></label>
                <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}"
                       class="form-control @error('telefono') is-invalid @enderror" placeholder="809-555-0000" autocomplete="tel">
                @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-sm-6 campos-agencia {{ $tipo === 'agencia' ? '' : 'd-none' }}">
                <label for="whatsapp" class="form-label">WhatsApp</label>
                <input type="tel" id="whatsapp" name="whatsapp" value="{{ old('whatsapp') }}"
                       class="form-control @error('whatsapp') is-invalid @enderror" placeholder="1 809 555 0000">
                @error('whatsapp') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="campos-agencia {{ $tipo === 'agencia' ? '' : 'd-none' }}">
            <div class="mb-3">
                <label for="direccion" class="form-label">{{ __('Dirección') }} <span class="opcional">({{ __('opcional') }})</span></label>
                <input type="text" id="direccion" name="direccion" value="{{ old('direccion') }}"
                       class="form-control @error('direccion') is-invalid @enderror" placeholder="{{ __('Calle, ciudad, provincia') }}" maxlength="200">
                @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">{{ __('Presentación de la agencia') }} <span class="opcional">({{ __('opcional') }})</span></label>
                <textarea id="descripcion" name="descripcion" rows="3" maxlength="1000"
                          class="form-control @error('descripcion') is-invalid @enderror"
                          placeholder="{{ __('¿Qué experiencias ofrecen y dónde?') }}">{{ old('descripcion') }}</textarea>
                @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <label for="contrasena" class="form-label">{{ __('Contraseña') }}</label>
                <div class="campo-contrasena">
                    <input type="password" id="contrasena" name="contrasena"
                           class="form-control @error('contrasena') is-invalid @enderror" autocomplete="new-password" required>
                    <button type="button" class="ver-contrasena" data-objetivo="contrasena" aria-label="{{ __('Mostrar contraseña') }}"><i class="bi bi-eye"></i></button>
                    @error('contrasena') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-sm-6">
                <label for="contrasena_confirmation" class="form-label">{{ __('Repite la contraseña') }}</label>
                <input type="password" id="contrasena_confirmation" name="contrasena_confirmation" class="form-control" autocomplete="new-password" required>
            </div>
            <div class="col-12">
                <small class="text-muted"><i class="bi bi-shield-lock"></i> {{ __('Mínimo 8 caracteres, con letras y números.') }}</small>
            </div>
        </div>

        <button type="submit" class="btn btn-turquesa w-100 btn-auth" id="botonRegistro">
            {{ $tipo === 'agencia' ? __('Registrar mi agencia') : __('Crear mi cuenta') }}
        </button>
    </form>

    <p class="auth-pie">{{ __('¿Ya tienes cuenta?') }} <a href="{{ route('login') }}">{{ __('Inicia sesión') }}</a></p>
@endsection
