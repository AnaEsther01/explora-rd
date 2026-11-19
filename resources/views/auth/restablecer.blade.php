@extends('layouts.auth')

@section('titulo', __('Nueva contraseña').' — Explora RD')
@section('lado_titulo', __('Crea una contraseña nueva'))
@section('lado_texto', __('Elige una contraseña segura que no uses en otras páginas.'))

@section('contenido')
    <h2 class="auth-titulo">{{ __('Nueva contraseña') }}</h2>
    <p class="auth-subtitulo">{{ __('Mínimo 8 caracteres, con letras y números.') }}</p>

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="correo" class="form-label">{{ __('Correo electrónico') }}</label>
            <input type="email" id="correo" name="correo" value="{{ old('correo', $correo) }}"
                   class="form-control @error('correo') is-invalid @enderror" autocomplete="email" required>
            @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label for="contrasena" class="form-label">{{ __('Contraseña nueva') }}</label>
            <div class="campo-contrasena">
                <input type="password" id="contrasena" name="contrasena"
                       class="form-control @error('contrasena') is-invalid @enderror" autocomplete="new-password" required autofocus>
                <button type="button" class="ver-contrasena" data-objetivo="contrasena" aria-label="{{ __('Mostrar contraseña') }}"><i class="bi bi-eye"></i></button>
                @error('contrasena') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="mb-4">
            <label for="contrasena_confirmation" class="form-label">{{ __('Repite la contraseña') }}</label>
            <input type="password" id="contrasena_confirmation" name="contrasena_confirmation" class="form-control" autocomplete="new-password" required>
        </div>
        <button type="submit" class="btn btn-turquesa w-100 btn-auth">{{ __('Guardar contraseña') }}</button>
    </form>
@endsection
