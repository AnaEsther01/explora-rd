@extends('layouts.app')

@section('titulo', $excursion->titulo.' — '.$excursion->destino->nombre.' | Explora RD')
@section('descripcion', \Illuminate\Support\Str::limit($excursion->descripcion, 155))

@php
    $agencia = $excursion->agencia;
    $nombreAgencia = $agencia->nombre_comercial ?: $agencia->nombre;
    $categoria = \App\Models\Excursion::CATEGORIAS[$excursion->categoria] ?? null;
    // Fotos: la de la excursión y la del destino (las que existan)
    $fotos = collect([$excursion->foto, $excursion->destino->foto])
        ->filter(fn ($f) => $f && file_exists(public_path($f)))->unique()->values();
    $iniciales = collect(explode(' ', $nombreAgencia))->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
@endphp

@section('contenido')

@include('partials.cabecera', [
    'foto' => $excursion->foto ?: $excursion->destino->foto,
    'alta' => true,
    'etiqueta' => e($excursion->nombreTipo()).($categoria ? ' · <i class="bi '.$categoria['icono'].'"></i> '.e(__($categoria['nombre'])) : ''),
    'titulo' => $excursion->titulo,
    'subtitulo' => $excursion->ubicacion,
    'migas' => [
        __('Inicio') => route('inicio'),
        __('Excursiones') => route('excursiones.index'),
        $excursion->destino->nombre => route('destinos.show', $excursion->destino),
        $excursion->titulo => null,
    ],
])

<section class="seccion">
    <div class="container">
        <div class="row g-4 g-lg-5">

            {{-- ===== Información de la excursión ===== --}}
            <div class="col-lg-8">

                {{-- Datos rápidos --}}
                <div class="datos-rapidos">
                    <div><i class="bi bi-clock"></i><span>{{ __('Duración') }}</span><strong>{{ $excursion->duracion ?: __('Consultar') }}</strong></div>
                    <div><i class="bi bi-geo-alt"></i><span>{{ __('Ubicación') }}</span><strong>{{ $excursion->ubicacion ?: $excursion->destino->nombre }}</strong></div>
                    <div><i class="bi bi-signpost-split"></i><span>{{ __('Tipo') }}</span><strong>{{ $excursion->nombreTipo() }}</strong></div>
                    <div><i class="bi bi-tag"></i><span>{{ __('Precio') }}</span><strong>{{ $excursion->precioFormateado() }}</strong></div>
                </div>

                <h2 class="subtitulo-lora mt-5 mb-3">{{ __('Descripción') }}</h2>
                <p class="texto-largo">{{ $excursion->descripcion }}</p>
                @unless (app()->getLocale() === 'es')
                    <p class="small text-muted"><i class="bi bi-translate"></i> {{ __('La agencia escribió esta descripción en español.') }}</p>
                @endunless

                @if ($fotos->isNotEmpty())
                    <h2 class="subtitulo-lora mt-5 mb-3">{{ __('Fotos') }}</h2>
                    <div class="fotos-destino">
                        @foreach ($fotos as $foto)
                            <img src="{{ asset($foto) }}" alt="{{ __('Foto de :lugar', ['lugar' => $excursion->titulo]) }}" loading="lazy">
                        @endforeach
                    </div>
                @endif

                <h2 class="subtitulo-lora mt-5 mb-3">{{ __('Información importante') }}</h2>
                <ul class="lista-actividades">
                    <li><i class="bi bi-info-circle"></i> {{ __('El precio es por persona y puede variar según la temporada; confírmalo con la agencia.') }}</li>
                    <li><i class="bi bi-chat-dots"></i> {{ __('La agencia te indicará el punto de encuentro, la hora de salida y qué llevar.') }}</li>
                    <li><i class="bi bi-arrow-repeat"></i> {{ __('Consulta la política de cancelación de la agencia antes de reservar.') }}</li>
                </ul>

                {{-- ===== Reseñas de los viajeros ===== --}}
                @php
                    $promedio = $excursion->promedioEstrellas();
                    $totalResenas = $resenas->count() ? $excursion->totalResenas() : 0;
                    $estrellasElegidas = (int) old('estrellas', $miResena?->estrellas);
                @endphp
                <h2 class="subtitulo-lora mt-5 mb-3" id="resenas">{{ __('Opiniones de viajeros') }}</h2>

                @if ($totalResenas)
                    <div class="resumen-resenas">
                        <div class="resumen-resenas-nota">
                            <strong>{{ number_format($promedio, 1) }}</strong>
                            @include('partials.estrellas', ['valor' => $promedio])
                            <span>{{ trans_choice(':count reseña|:count reseñas', $totalResenas) }}</span>
                        </div>
                        <div class="resumen-resenas-barras">
                            @for ($e = 5; $e >= 1; $e--)
                                @php $cantidad = (int) ($porEstrellas[$e] ?? 0); @endphp
                                <div class="barra-estrellas">
                                    <span>{{ $e }} <i class="bi bi-star-fill"></i></span>
                                    <div class="barra"><div style="width: {{ round($cantidad / $totalResenas * 100) }}%"></div></div>
                                    <small>{{ $cantidad }}</small>
                                </div>
                            @endfor
                        </div>
                    </div>
                @else
                    <p class="text-muted">{{ __('Todavía no hay opiniones. ¡Sé el primero en contar tu experiencia!') }}</p>
                @endif

                {{-- Formulario: solo para quien ya hizo la excursión --}}
                @if ($puedeResenar)
                    <form method="POST" action="{{ route('resenas.store', $excursion) }}" class="caja-resena-form" novalidate>
                        @csrf
                        <h3>{{ $miResena ? __('Edita tu reseña') : __('¿Qué te pareció?') }}</h3>
                        <fieldset class="mb-2">
                            <legend class="form-label">{{ __('Tu calificación') }}</legend>
                            {{-- Van de 5 a 1 porque el CSS pinta «esta y las anteriores» al pasar el ratón --}}
                            <div class="elegir-estrellas">
                                @for ($e = 5; $e >= 1; $e--)
                                    <input type="radio" name="estrellas" id="estrella{{ $e }}" value="{{ $e }}" @checked($estrellasElegidas === $e)>
                                    <label for="estrella{{ $e }}" title="{{ trans_choice(':count estrella|:count estrellas', $e) }}">
                                        <i class="bi bi-star-fill"></i><span class="visually-hidden">{{ trans_choice(':count estrella|:count estrellas', $e) }}</span>
                                    </label>
                                @endfor
                            </div>
                        </fieldset>
                        @error('estrellas') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                        <label for="comentarioResena" class="form-label">{{ __('Comentario') }} <span class="opcional">({{ __('opcional') }})</span></label>
                        <textarea id="comentarioResena" name="comentario" rows="3" maxlength="600"
                                  class="form-control @error('comentario') is-invalid @enderror"
                                  placeholder="{{ __('Cuenta qué te gustó y qué se podría mejorar.') }}">{{ old('comentario', $miResena?->comentario) }}</textarea>
                        @error('comentario') <div class="invalid-feedback">{{ $message }}</div> @enderror

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button type="submit" class="btn btn-turquesa"><i class="bi bi-send"></i> {{ $miResena ? __('Guardar cambios') : __('Publicar reseña') }}</button>
                            @if ($miResena)
                                <button type="submit" form="borrarResena" class="btn btn-contorno-peligro"><i class="bi bi-trash"></i> {{ __('Borrar') }}</button>
                            @endif
                        </div>
                        @if ($miResena && ! $miResena->visible)
                            <p class="small text-muted mt-2 mb-0"><i class="bi bi-eye-slash"></i> {{ __('El equipo de Explora RD ocultó tu reseña porque no cumple las normas.') }}</p>
                        @endif
                    </form>
                    @if ($miResena)
                        <form id="borrarResena" method="POST" action="{{ route('resenas.destroy', $excursion) }}"
                              onsubmit="return confirm('{{ addslashes(__('¿Borrar tu reseña?')) }}')">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                @elseif (auth()->user()?->esTurista())
                    <p class="aviso-reserva"><i class="bi bi-info-circle"></i> {{ __('Podrás opinar cuando hayas hecho esta excursión con una reserva confirmada.') }}</p>
                @endif

                @foreach ($resenas as $resena)
                    <article class="resena">
                        <div class="resena-cabecera">
                            <span class="resena-avatar">{{ mb_strtoupper(mb_substr($resena->usuario->nombre, 0, 1)) }}</span>
                            <div>
                                <strong>{{ $resena->nombreAutor() }}</strong>
                                <small>{{ $resena->created_at->translatedFormat('F Y') }}</small>
                            </div>
                            @include('partials.estrellas', ['valor' => $resena->estrellas])
                        </div>
                        @if ($resena->comentario)
                            <p>{{ $resena->comentario }}</p>
                        @endif
                    </article>
                @endforeach

                {{-- Destino --}}
                <a href="{{ route('destinos.show', $excursion->destino) }}" class="tarjeta-destino-horizontal mt-5">
                    <div class="tarjeta-destino-horizontal-foto">
                        @include('partials.foto', ['ruta' => $excursion->destino->foto, 'tipo' => $excursion->destino->tipo, 'alt' => $excursion->destino->nombre])
                    </div>
                    <div>
                        <span class="seccion-etiqueta mb-1">{{ __('El destino') }}</span>
                        <h3>{{ $excursion->destino->nombre }}</h3>
                        <p>{{ $excursion->destino->resumen() }} <span class="enlace-ver-todo">{{ __('Conocer el destino') }} <i class="bi bi-arrow-right"></i></span></p>
                    </div>
                </a>
            </div>

            {{-- ===== Caja de precio y contacto (se queda fija al bajar) ===== --}}
            <aside class="col-lg-4">
                <div class="caja-reserva">
                    <div class="caja-reserva-precio">
                        <small>{{ __('Desde') }}</small>
                        <strong>{{ $excursion->precioFormateado() }}</strong>
                        <small>{{ __('por persona') }}</small>
                        @if ($excursion->totalResenas())
                            <a href="#resenas" class="caja-reserva-valoracion">
                                <i class="bi bi-star-fill"></i> {{ number_format($excursion->promedioEstrellas(), 1) }}
                                · {{ trans_choice(':count reseña|:count reseñas', $excursion->totalResenas()) }}
                            </a>
                        @endif
                    </div>

                    @php
                        $usuario = auth()->user();
                        $puedeReservar = $usuario?->esTurista();
                        $personasElegidas = (int) old('cantidad_personas', $personas);
                        $manana = now()->addDay()->format('Y-m-d');
                    @endphp

                    {{-- Formulario de reserva (diagrama de flujo: «Llena y envía la solicitud de reserva») --}}
                    <form id="formConsulta" class="caja-reserva-form" method="POST" novalidate
                          action="{{ route('reservas.store', $excursion) }}"
                          data-whatsapp="{{ $excursion->whatsappAgencia() }}" data-titulo="{{ $excursion->titulo }}">
                        @csrf
                        <div class="mb-3">
                            <label for="consultaFecha" class="form-label">{{ __('Fecha') }}</label>
                            <input type="date" class="form-control @error('fecha') is-invalid @enderror" id="consultaFecha" name="fecha"
                                   min="{{ $manana }}" value="{{ old('fecha', $fecha) }}" required>
                            @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="consultaPersonas" class="form-label">{{ __('Personas') }}</label>
                            <select class="form-select @error('cantidad_personas') is-invalid @enderror" id="consultaPersonas" name="cantidad_personas"
                                    data-precio="{{ (float) $excursion->precio }}">
                                @for ($i = 1; $i <= 15; $i++)
                                    <option value="{{ $i }}" @selected($personasElegidas === $i)>{{ trans_choice(':count persona|:count personas', $i) }}</option>
                                @endfor
                            </select>
                            @error('cantidad_personas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @if ($puedeReservar)
                            <div class="mb-3">
                                <label for="consultaComentario" class="form-label">{{ __('Comentario') }} <span class="opcional">({{ __('opcional') }})</span></label>
                                <textarea class="form-control @error('comentario') is-invalid @enderror" id="consultaComentario" name="comentario"
                                          rows="2" maxlength="500" placeholder="{{ __('¿Algo que la agencia deba saber?') }}">{{ old('comentario') }}</textarea>
                                @error('comentario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif
                        <div class="caja-reserva-total">
                            <span>{{ __('Total estimado') }}</span>
                            <strong id="totalEstimado">RD$ {{ number_format($excursion->precio * $personasElegidas, 0, '.', ',') }}</strong>
                        </div>

                        @if ($puedeReservar)
                            <button type="submit" class="btn btn-turquesa w-100 btn-auth">
                                <i class="bi bi-calendar-check"></i> {{ __('Solicitar reserva') }}
                            </button>
                            <p class="small text-muted text-center mt-2 mb-3">{{ __('No pagas nada ahora. La agencia confirma la disponibilidad.') }}</p>
                        @elseif (! $usuario)
                            <a href="{{ route('login', ['volver' => request()->getRequestUri()]) }}" class="btn btn-turquesa w-100 btn-auth">
                                <i class="bi bi-box-arrow-in-right"></i> {{ __('Inicia sesión para reservar') }}
                            </a>
                            <p class="small text-muted text-center mt-2 mb-3">{{ __('¿No tienes cuenta?') }} <a href="{{ route('registro') }}">{{ __('Regístrate gratis') }}</a></p>
                        @else
                            <p class="aviso-reserva"><i class="bi bi-info-circle"></i> {{ __('Las reservas las hacen las cuentas de turista.') }}</p>
                        @endif

                        <button type="button" id="btnConsultaWhatsapp" class="btn btn-whatsapp-ancho w-100">
                            <i class="bi bi-whatsapp"></i> {{ __('Consultar por WhatsApp') }}
                        </button>
                    </form>
                    {{-- El corazón va fuera del formulario de reserva (un formulario no puede ir dentro de otro) --}}
                    <div class="caja-reserva-favorito">@include('partials.boton-favorito', ['excursion' => $excursion, 'grande' => true])</div>
                </div>

                {{-- Agencia --}}
                <div class="caja-agencia">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        @if ($agencia->tieneLogo())
                            <img src="{{ asset($agencia->logo) }}" alt="{{ __('Logo de :nombre', ['nombre' => $nombreAgencia]) }}" class="agencia-logo">
                        @else
                            <span class="agencia-logo agencia-iniciales" aria-hidden="true">{{ $iniciales }}</span>
                        @endif
                        <div>
                            <small class="text-muted">{{ __('Organiza') }}</small>
                            <h3 class="agencia-nombre">{{ $nombreAgencia }}</h3>
                        </div>
                    </div>
                    @if ($agencia->descripcion)
                        <p class="small text-muted">{{ $agencia->descripcion }}</p>
                    @endif
                    <ul class="datos-agencia list-unstyled">
                        @if ($agencia->telefono)
                            <li><i class="bi bi-telephone"></i> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $agencia->telefono) }}">{{ $agencia->telefono }}</a></li>
                        @endif
                        @if ($agencia->whatsapp)
                            <li><i class="bi bi-whatsapp"></i> <a href="{{ $excursion->enlaceWhatsapp() }}" target="_blank" rel="noopener">WhatsApp</a></li>
                        @endif
                        <li><i class="bi bi-envelope"></i> <a href="mailto:{{ $agencia->correo }}">{{ $agencia->correo }}</a></li>
                        @if ($agencia->direccion)
                            <li><i class="bi bi-geo-alt"></i> {{ $agencia->direccion }}</li>
                        @endif
                    </ul>
                    <a href="{{ route('agencias.show', $agencia) }}" class="btn btn-contorno w-100 mt-2">
                        <i class="bi bi-building"></i> {{ __('Ver perfil de la agencia') }}
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>

@if ($relacionadas->isNotEmpty())
    <section class="seccion seccion-clara">
        <div class="container">
            <div class="seccion-encabezado">
                <div>
                    <span class="seccion-etiqueta">{{ __('También te puede gustar') }}</span>
                    <h2 class="seccion-titulo">{{ __('Más experiencias') }}</h2>
                </div>
                <a href="{{ route('excursiones.index') }}" class="enlace-ver-todo">{{ __('Ver todas') }} <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="row g-4">
                @foreach ($relacionadas as $otra)
                    <div class="col-12 col-md-6 col-lg-4">
                        @include('partials.tarjeta-excursion', ['excursion' => $otra])
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@endsection
