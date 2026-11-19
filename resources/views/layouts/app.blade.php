<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body>
    @include('partials.navbar')

    <main>
        @include('partials.alertas')
        @yield('contenido')
    </main>

    @include('partials.footer')
    {{-- El asistente con IA sale en las páginas públicas, no en los paneles de agencia o administrador --}}
    @unless (request()->routeIs('agencia.*', 'admin.*'))
        @include('partials.asistente')
    @endunless
    @include('partials.whatsapp')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.textos-js')
    <script src="{{ asset('js/app.js') }}"></script>
    {{-- Programas extra que solo necesitan algunas páginas (por ejemplo, el mapa) --}}
    @stack('scripts')
</body>
</html>
