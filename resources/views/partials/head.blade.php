{{-- Parte <head> que comparten todas las plantillas --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('titulo', __('Explora RD — Descubre República Dominicana'))</title>
<meta name="description" content="@yield('descripcion', __('Descubre destinos, encuentra excursiones y contacta directamente con agencias turísticas de República Dominicana.'))">
<link rel="icon" type="image/svg+xml" href="{{ asset('img/logo.svg') }}">

{{-- Letras de Google Fonts: Lora (títulos) y Poppins (textos y botones) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

{{-- Bootstrap 5 e iconos de Bootstrap --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

{{-- Estilos extra que solo necesitan algunas páginas (por ejemplo, el mapa) --}}
@stack('estilos')

{{-- Nuestros estilos (colores y diseño de Explora RD) --}}
<link href="{{ asset('css/estilos.css') }}" rel="stylesheet">
