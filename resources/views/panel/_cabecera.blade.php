{{-- Cabecera compacta de los paneles. Variables: $titulo, $subtitulo, $icono --}}
<header class="panel-cabecera">
    <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="panel-icono"><i class="bi {{ $icono ?? 'bi-person' }}"></i></span>
            <div>
                <h1 class="panel-titulo">{{ $titulo }}</h1>
                <p class="panel-subtitulo mb-0">{{ $subtitulo }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-pastilla-clara"><i class="bi bi-box-arrow-right"></i> {{ __('Cerrar sesión') }}</button>
        </form>
    </div>
</header>
