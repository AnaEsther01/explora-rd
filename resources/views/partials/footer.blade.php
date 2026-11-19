{{-- Pie de página --}}
<footer class="pie">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                @include('partials.logo')
                <p class="mt-3 pie-texto">
                    {{ __('La plataforma turística de República Dominicana: descubre destinos, encuentra excursiones y contacta directamente con agencias locales.') }}
                </p>
                <div class="redes">
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                    <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h3 class="pie-titulo">{{ __('Explora') }}</h3>
                <ul class="list-unstyled pie-enlaces">
                    <li><a href="{{ route('destinos.index') }}">{{ __('Destinos') }}</a></li>
                    <li><a href="{{ route('excursiones.index') }}">{{ __('Excursiones') }}</a></li>
                    <li><a href="{{ route('excursiones.index', ['tipo' => 'circuitos']) }}">{{ __('Circuitos y paquetes') }}</a></li>
                    <li><a href="{{ route('servicios.asesorias') }}">{{ __('Asesorías') }}</a></li>
                    <li><a href="{{ route('servicios.vehiculos') }}">{{ __('Renta de vehículos') }}</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h3 class="pie-titulo">Explora RD</h3>
                <ul class="list-unstyled pie-enlaces">
                    <li><a href="{{ route('inicio') }}#nosotros">{{ __('Sobre nosotros') }}</a></li>
                    <li><a href="{{ route('galeria') }}">{{ __('Galería') }}</a></li>
                    <li><a href="{{ route('inicio') }}#preguntas">{{ __('Preguntas frecuentes') }}</a></li>
                    <li><a href="{{ route('registro', ['tipo' => 'agencia']) }}">{{ __('Para agencias') }}</a></li>
                </ul>
            </div>

            <div class="col-lg-4">
                <h3 class="pie-titulo">{{ __('Contacto') }}</h3>
                <ul class="list-unstyled pie-enlaces">
                    <li><i class="bi bi-whatsapp"></i> <a href="https://wa.me/{{ config('explora.whatsapp') }}" target="_blank" rel="noopener">{{ config('explora.telefono') }}</a></li>
                    <li><i class="bi bi-envelope"></i> <a href="mailto:{{ config('explora.correo') }}">{{ config('explora.correo') }}</a></li>
                    <li><i class="bi bi-geo-alt"></i> {{ __(config('explora.direccion')) }}</li>
                </ul>
            </div>
        </div>

        <hr class="pie-linea">
        <p class="pie-copy mb-0">© {{ date('Y') }} Explora RD · {{ __('Proyecto de') }} Ana Esther Hernández Hernández · Politécnico Cristiano James Davis</p>
        <p class="pie-copy mb-0 mt-1">{{ __('Fotos') }}: <a href="https://unsplash.com" target="_blank" rel="noopener">Unsplash</a> {{ __('y') }} <a href="https://www.pexels.com" target="_blank" rel="noopener">Pexels</a> ({{ __('créditos en') }} img/CREDITOS.txt)</p>
    </div>
</footer>
