<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
{{-- Plantilla de dos columnas para Entrar y Registro (maqueta 18):
     a la izquierda el mar turquesa con la palmera; a la derecha el formulario. --}}
<head>
    @include('partials.head')
</head>
<body class="cuerpo-auth">
    <div class="auth">
        <aside class="auth-lado">
            <a href="{{ route('inicio') }}" class="logo auth-logo d-inline-flex align-items-center text-decoration-none">
                <img src="{{ asset('img/logo.svg') }}" alt="" width="48" height="48" class="logo-icono">
                <span class="logo-texto">Explora RD</span>
            </a>

            <div class="auth-lado-texto">
                <h1>@yield('lado_titulo', __('Bienvenido de nuevo'))</h1>
                <p>@yield('lado_texto', __('Inicia sesión para reservar excursiones o administrar el perfil de tu agencia turística.'))</p>
            </div>

            <img src="{{ asset('img/palmera.svg') }}" alt="" class="auth-palmera" aria-hidden="true">
        </aside>

        <main class="auth-principal">
            <a href="{{ route('inicio') }}" class="auth-volver"><i class="bi bi-arrow-left"></i> {{ __('Volver al inicio') }}</a>
            @include('partials.alertas')
            <div class="auth-tarjeta">
                @yield('contenido')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.textos-js')
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
