@extends('layouts.agencia')

@php
    $editando = $excursion->exists;
    $esPaquete = in_array(old('tipo', $excursion->tipo), ['circuito', 'paquete']);
@endphp

@section('titulo', ($editando ? 'Editar «'.$excursion->titulo.'»' : 'Nueva excursión').' — Panel de la agencia | Explora RD')
@section('seccion', $esPaquete ? 'paquetes' : 'excursiones')

@section('panel')
<form method="POST" enctype="multipart/form-data" novalidate
      action="{{ $editando ? route('agencia.excursiones.update', $excursion) : route('agencia.excursiones.store') }}">
    @csrf
    @if ($editando) @method('PUT') @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="subtitulo-lora mb-0">{{ $editando ? 'Editar: '.$excursion->titulo : 'Publicar una experiencia nueva' }}</h2>
        <a href="{{ route('agencia.excursiones.index', $esPaquete ? ['ver' => 'paquetes'] : []) }}" class="enlace-suave">
            <i class="bi bi-arrow-left"></i> Volver a la lista
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alerta alerta-error mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Revisa los campos marcados en rojo.</span>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            {{-- ===== Información principal ===== --}}
            <div class="caja-panel mb-4">
                <h3 class="caja-lateral-titulo">Información</h3>

                <div class="mb-3">
                    <label for="titulo" class="form-label">Título</label>
                    <input type="text" id="titulo" name="titulo" maxlength="150" value="{{ old('titulo', $excursion->titulo) }}"
                           class="form-control @error('titulo') is-invalid @enderror" placeholder="Ej.: Paseo en catamarán a isla Saona">
                    @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select id="tipo" name="tipo" class="form-select @error('tipo') is-invalid @enderror">
                            @foreach (\App\Models\Excursion::TIPOS as $clave => $nombre)
                                <option value="{{ $clave }}" @selected(old('tipo', $excursion->tipo) === $clave)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                        @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="categoria" class="form-label">Categoría</label>
                        <select id="categoria" name="categoria" class="form-select @error('categoria') is-invalid @enderror">
                            <option value="">Sin categoría</option>
                            @foreach (\App\Models\Excursion::CATEGORIAS as $clave => $cat)
                                <option value="{{ $clave }}" @selected(old('categoria', $excursion->categoria) === $clave)>{{ $cat['nombre'] }}</option>
                            @endforeach
                        </select>
                        @error('categoria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="destino_id" class="form-label">Destino</label>
                        <select id="destino_id" name="destino_id" class="form-select @error('destino_id') is-invalid @enderror">
                            <option value="">Elige un destino</option>
                            @foreach ($destinos as $destino)
                                <option value="{{ $destino->id }}" @selected((int) old('destino_id', $excursion->destino_id) === $destino->id)>{{ $destino->nombre }}</option>
                            @endforeach
                        </select>
                        @error('destino_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div>
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="6" maxlength="3000" data-contador="contadorDescripcion"
                              class="form-control @error('descripcion') is-invalid @enderror"
                              placeholder="¿Qué incluye? ¿Qué van a ver y hacer? ¿Qué deben llevar?">{{ old('descripcion', $excursion->descripcion) }}</textarea>
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted"><span id="contadorDescripcion">0</span> / 3000 caracteres</small>
                </div>
            </div>

            {{-- ===== Precio, duración y ubicación ===== --}}
            <div class="caja-panel">
                <h3 class="caja-lateral-titulo">Precio y detalles</h3>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="precio" class="form-label">Precio por persona (RD$)</label>
                        <input type="number" id="precio" name="precio" min="0" step="50" value="{{ old('precio', $excursion->precio !== null ? (float) $excursion->precio : '') }}"
                               class="form-control @error('precio') is-invalid @enderror" placeholder="3500">
                        @error('precio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="duracion" class="form-label">Duración</label>
                        <input type="text" id="duracion" name="duracion" maxlength="50" value="{{ old('duracion', $excursion->duracion) }}"
                               class="form-control @error('duracion') is-invalid @enderror" placeholder="Ej.: 5 horas, 2 días" list="duraciones">
                        <datalist id="duraciones">
                            <option value="3 horas"><option value="4 horas"><option value="Medio día"><option value="Día completo">
                            <option value="2 días"><option value="3 días / 2 noches">
                        </datalist>
                        @error('duracion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="ubicacion" class="form-label">Punto de salida</label>
                        <input type="text" id="ubicacion" name="ubicacion" maxlength="150" value="{{ old('ubicacion', $excursion->ubicacion) }}"
                               class="form-control @error('ubicacion') is-invalid @enderror" placeholder="Ej.: Bayahíbe, La Altagracia">
                        @error('ubicacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            {{-- ===== Foto ===== --}}
            <div class="caja-panel mb-4">
                <h3 class="caja-lateral-titulo">Foto principal</h3>
                <div class="foto-editor mb-3">
                    @if ($excursion->foto && file_exists(public_path($excursion->foto)))
                        <img src="{{ asset($excursion->foto) }}" alt="Foto actual" id="vistaFoto">
                    @else
                        <img src="" alt="Vista previa" id="vistaFoto" class="d-none">
                        <div class="foto-editor-vacia" id="vistaFotoVacia"><i class="bi bi-image"></i><span>Sin foto</span></div>
                    @endif
                </div>
                <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" data-vista="vistaFoto"
                       class="form-control @error('foto') is-invalid @enderror" aria-label="Elegir foto">
                @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="text-muted d-block mt-1">JPG, PNG o WEBP. Máximo 2 MB. Horizontal se ve mejor.</small>
                @if ($editando && $excursion->foto)
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="quitar_foto" name="quitar_foto" value="1">
                        <label class="form-check-label small" for="quitar_foto">Quitar la foto actual</label>
                    </div>
                @endif
            </div>

            {{-- ===== Publicación ===== --}}
            <div class="caja-panel">
                <h3 class="caja-lateral-titulo">Publicación</h3>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="disponible" name="disponible" value="1"
                           @checked($errors->any() ? old('disponible') : ($excursion->disponible ?? true))>
                    <label class="form-check-label" for="disponible">Visible para los turistas</label>
                </div>
                <button type="submit" class="btn btn-turquesa w-100">
                    <i class="bi bi-check-lg"></i> {{ $editando ? 'Guardar cambios' : 'Publicar' }}
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
