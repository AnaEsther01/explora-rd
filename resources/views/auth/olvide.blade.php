@extends('layouts.auth')

@section('titulo', __('Recuperar contraseña').' — Explora RD')
@section('lado_titulo', __('¿Olvidaste tu contraseña?'))
@section('lado_texto', __('No te preocupes. Te enviaremos un enlace a tu correo para que crees una nueva.'))

@section('contenido')
    <h2 class="auth-titulo">{{ __('Recuperar contraseña') }}</h2>
    <p class="auth-subtitulo">{{ __('Escribe el correo con el que te registraste') }}</p>

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf
        <div class="mb-4">
            <label for="correo" class="form-label">{{ __('Correo electrónico') }}</label>
            <input type="email" id="correo" name="correo" value="{{ old('correo') }}"
                   class="form-control @error('correo') is-invalid @enderror" placeholder="ana@correo.com" autocomplete="email" required autofocus>
            @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-turquesa w-100 btn-auth">{{ __('Enviar enlace') }}</button>
    </form>

    <p class="auth-pie"><a href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> {{ __('Volver a iniciar sesión') }}</a></p>
@endsection
