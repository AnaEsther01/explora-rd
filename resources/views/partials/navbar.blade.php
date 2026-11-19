{{-- Menú principal: logo y enlaces a la izquierda; idioma y «Entrar» a la derecha.
     Empieza transparente sobre la portada y se vuelve blanco al bajar.
     Los textos van dentro de __('...') para que se traduzcan (lang/en.json y lang/fr.json). --}}
@php
    $idiomas = \App\Http\Middleware\EstablecerIdioma::IDIOMAS;
    $idiomaActual = app()->getLocale();
@endphp
<nav id="menu" class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        @include('partials.logo')

        {{-- Botón de 3 rayas que aparece en el celular --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuEnlaces"
                aria-controls="menuEnlaces" aria-expanded="false" aria-label="{{ __('Abrir menú') }}">
            <i class="bi bi-list"></i>
        </button>

        <div class="collapse navbar-collapse" id="menuEnlaces">
            <ul class="navbar-nav me-auto ms-lg-4">
                <li class="nav-item"><a class="nav-link @if (request()->routeIs('inicio')) active @endif" href="{{ route('inicio') }}">{{ __('Inicio') }}</a></li>
                <li class="nav-item"><a class="nav-link @if (request()->routeIs('destinos.*')) active @endif" href="{{ route('destinos.index') }}">{{ __('Destinos') }}</a></li>
                <li class="nav-item"><a class="nav-link @if (request()->routeIs('excursiones.*')) active @endif" href="{{ route('excursiones.index') }}">{{ __('Excursiones') }}</a></li>
                <li class="nav-item"><a class="nav-link @if (request()->routeIs('agencias.*')) active @endif" href="{{ route('agencias.index') }}">{{ __('Agencias') }}</a></li>
                {{-- Servicios: Asesorías y Renta de vehículos --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle @if (request()->routeIs('servicios.*')) active @endif" href="#" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">{{ __('Servicios') }}</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('servicios.asesorias') }}"><i class="bi bi-chat-heart"></i> {{ __('Asesorías') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('servicios.vehiculos') }}"><i class="bi bi-car-front"></i> {{ __('Renta de vehículos') }}</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="{{ route('inicio') }}#contacto">{{ __('Contacto') }}</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2 menu-acciones">
                {{-- Selector de idioma --}}
                <div class="dropdown">
                    <button class="btn btn-pastilla dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                            aria-label="{{ __('Idioma') }}: {{ $idiomas[$idiomaActual]['nombre'] }}">
                        <i class="bi bi-globe2"></i> {{ strtoupper($idiomaActual) }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach ($idiomas as $codigo => $idioma)
                            <li>
                                <a class="dropdown-item {{ $codigo === $idiomaActual ? 'active' : '' }}" href="{{ route('idioma', $codigo) }}"
                                   hreflang="{{ $codigo }}" lang="{{ $codigo }}">
                                    {{ $idioma['bandera'] }} {{ $idioma['nombre'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @auth
                    {{-- Con sesión iniciada: menú con el nombre, «Mi panel» y «Cerrar sesión» --}}
                    <div class="dropdown">
                        <button class="btn btn-pastilla dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> {{ \Illuminate\Support\Str::limit(auth()->user()->nombreVisible(), 18) }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route(auth()->user()->rutaPanel()) }}"><i class="bi bi-speedometer2"></i> {{ __('Mi panel') }}</a></li>
                            @if (auth()->user()->esTurista())
                                <li><a class="dropdown-item" href="{{ route('turista.panel') }}#favoritos"><i class="bi bi-heart"></i> {{ __('Mis favoritos') }}</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> {{ __('Cerrar sesión') }}</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-pastilla"><i class="bi bi-box-arrow-in-right"></i> {{ __('Entrar') }}</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
