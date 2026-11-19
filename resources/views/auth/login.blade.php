@extends('layouts.auth')

@section('titulo', __('Iniciar sesión').' — Explora RD')

@section('contenido')
    <h2 class="auth-titulo">{{ __('Iniciar sesión') }}</h2>
    <p class="auth-subtitulo">{{ __('Ingresa con tu correo y contraseña') }}</p>

    <form method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf {{-- protección contra formularios falsos (obligatoria en Laravel) --}}

        <div class="mb-3">
            <label for="correo" class="form-label">{{ __('Correo electrónico') }}</label>
            <input type="email" id="correo" name="correo" value="{{ old('correo') }}"
                   class="form-control @error('correo') is-invalid @enderror"
                   placeholder="ana@correo.com" autocomplete="email" required autofocus>
            @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="contrasena" class="form-label">{{ __('Contraseña') }}</label>
            <div class="campo-contrasena">
                <input type="password" id="contrasena" name="contrasena"
                       class="form-control @error('contrasena') is-invalid @enderror"
                       placeholder="••••••••" autocomplete="current-password" required>
                <button type="button" class="ver-contrasena" data-objetivo="contrasena" aria-label="{{ __('Mostrar contraseña') }}">
                    <i class="bi bi-eye"></i>
                </button>
                @error('contrasena') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 small">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="recordarme" name="recordarme" value="1" @checked(old('recordarme'))>
                <label class="form-check-label" for="recordarme">{{ __('Recordarme') }}</label>
            </div>
            <a href="{{ route('password.request') }}" class="enlace-suave">{{ __('¿Olvidaste tu contraseña?') }}</a>
        </div>

        <button type="submit" class="btn btn-turquesa w-100 btn-auth">{{ __('Entrar') }}</button>
    </form>

    <p class="auth-pie">
        {{ __('¿No tienes cuenta?') }} <a href="{{ route('registro') }}">{{ __('Regístrate') }}</a>
        · {{ __('¿Eres agencia?') }} <a href="{{ route('registro', ['tipo' => 'agencia']) }}">{{ __('Únete aquí') }}</a>
    </p>
@endsection
