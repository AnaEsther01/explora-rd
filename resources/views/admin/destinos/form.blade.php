@extends('layouts.admin')

@php $editando = $destino->exists; @endphp

@section('titulo', ($editando ? 'Editar '.$destino->nombre : 'Nuevo destino').' — Panel del administrador | Explora RD')
@section('seccion', 'destinos')

@section('panel')
<form method="POST" enctype="multipart/form-data" novalidate
      action="{{ $editando ? route('admin.destinos.update', $destino) : route('admin.destinos.store') }}">
    @csrf
    @if ($editando) @method('PUT') @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="subtitulo-lora mb-0">{{ $editando ? 'Editar: '.$destino->nombre : 'Nuevo destino' }}</h2>
        <a href="{{ route('admin.destinos.index') }}" class="enlace-suave"><i class="bi bi-arrow-left"></i> Volver a la lista</a>
    </div>

    @if ($errors->any())
        <div class="alert alerta alerta-error mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i><span>Revisa los campos marcados en rojo.</span>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="caja-panel">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre del destino</label>
                        <input type="text" id="nombre" name="nombre" maxlength="100" value="{{ old('nombre', $destino->nombre) }}"
                               class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej.: Las Terrenas">
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="provincia" class="form-label">Provincia</label>
                        <input type="text" id="provincia" name="provincia" maxlength="100" value="{{ old('provincia', $destino->provincia) }}"
                               class="form-control @error('provincia') is-invalid @enderror" placeholder="Ej.: Samaná">
                        @error('provincia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select id="tipo" name="tipo" class="form-select @error('tipo') is-invalid @enderror">
                            @foreach (\App\Models\Destino::TIPOS as $clave => $t)
                                <option value="{{ $clave }}" @selected(old('tipo', $destino->tipo) === $clave)>{{ $t['nombre'] }}</option>
                            @endforeach
                        </select>
                        @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    {{-- Ubicación en el mapa (opcional) --}}
                    <div class="col-md-6">
                        <label for="latitud" class="form-label">Latitud <span class="opcional">(para el mapa)</span></label>
                        <input type="number" step="0.000001" id="latitud" name="latitud" value="{{ old('latitud', $destino->latitud) }}"
                               class="form-control @error('latitud') is-invalid @enderror" placeholder="Ej.: 19.1167">
                        @error('latitud') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="longitud" class="form-label">Longitud <span class="opcional">(para el mapa)</span></label>
                        <input type="number" step="0.000001" id="longitud" name="longitud" value="{{ old('longitud', $destino->longitud) }}"
                               class="form-control @error('longitud') is-invalid @enderror" placeholder="Ej.: -70.6333">
                        @error('longitud') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <small class="text-muted"><i class="bi bi-info-circle"></i> Para saberlas: en Google Maps, haz clic derecho sobre el lugar y copia los dos números (el primero es la latitud y el segundo la longitud). Si las dejas vacías, el destino no sale en el mapa.</small>
                    </div>
                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="5" maxlength="3000" data-contador="contadorDescripcion"
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  placeholder="La primera oración se usa como resumen en las tarjetas.">{{ old('descripcion', $destino->descripcion) }}</textarea>
                        @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted"><span id="contadorDescripcion">0</span> / 3000 caracteres. La primera oración (hasta el primer punto) sale como resumen.</small>
                    </div>
                    <div class="col-12">
                        <label for="actividades" class="form-label">Actividades <small class="text-muted">(una por línea)</small></label>
                        <textarea id="actividades" name="actividades" rows="5" class="form-control @error('actividades') is-invalid @enderror"
                                  placeholder="Paseo en bote&#10;Snorkel&#10;Senderismo">{{ old('actividades', implode("\n", $destino->actividades ?? [])) }}</textarea>
                        @error('actividades') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Traducciones (opcional): si se deja vacío, ese idioma muestra el texto en español --}}
            <div class="caja-panel mt-4">
                <h3 class="caja-lateral-titulo mb-1">Traducciones <span class="opcional">(opcional)</span></h3>
                <p class="small text-muted">Lo que se escriba aquí se muestra cuando el visitante elige ese idioma. Si se deja vacío, se muestra el texto en español.</p>
                @foreach (\App\Models\Destino::IDIOMAS_TRADUCCION as $idioma => $nombreIdioma)
                    @php $tr = $destino->traducciones[$idioma] ?? []; @endphp
                    <details class="traduccion-destino" @if (! empty($tr)) open @endif>
                        <summary>{{ $idioma === 'en' ? '🇺🇸' : '🇫🇷' }} {{ $nombreIdioma }}</summary>
                        <div class="mt-3">
                            <label for="tr_{{ $idioma }}_descripcion" class="form-label">Descripción en {{ strtolower($nombreIdioma) }}</label>
                            <textarea id="tr_{{ $idioma }}_descripcion" name="traducciones[{{ $idioma }}][descripcion]" rows="4" maxlength="3000"
                                      class="form-control mb-3" lang="{{ $idioma }}">{{ old("traducciones.$idioma.descripcion", $tr['descripcion'] ?? '') }}</textarea>
                            <label for="tr_{{ $idioma }}_actividades" class="form-label">Actividades en {{ strtolower($nombreIdioma) }} <small class="text-muted">(una por línea)</small></label>
                            <textarea id="tr_{{ $idioma }}_actividades" name="traducciones[{{ $idioma }}][actividades]" rows="4"
                                      class="form-control" lang="{{ $idioma }}">{{ old("traducciones.$idioma.actividades", implode("\n", $tr['actividades'] ?? [])) }}</textarea>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>

        <div class="col-xl-4">
            <div class="caja-panel mb-4">
                <h3 class="caja-lateral-titulo">Foto</h3>
                <div class="foto-editor mb-3">
                    @if ($destino->foto && file_exists(public_path($destino->foto)))
                        <img src="{{ asset($destino->foto) }}" alt="Foto actual" id="vistaFoto">
                    @else
                        <img src="" alt="Vista previa" id="vistaFoto" class="d-none">
                        <div class="foto-editor-vacia" id="vistaFotoVacia"><i class="bi bi-image"></i><span>Sin foto</span></div>
                    @endif
                </div>
                <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" data-vista="vistaFoto"
                       class="form-control @error('foto') is-invalid @enderror" aria-label="Elegir foto">
                @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="text-muted d-block mt-1">JPG, PNG o WEBP. Máximo 2 MB. Usa fotos propias o con permiso, y anota el autor en img/CREDITOS.txt.</small>
                @if ($editando && $destino->foto)
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="quitar_foto" name="quitar_foto" value="1">
                        <label class="form-check-label small" for="quitar_foto">Quitar la foto actual</label>
                    </div>
                @endif
            </div>
            <button type="submit" class="btn btn-turquesa w-100"><i class="bi bi-check-lg"></i> {{ $editando ? 'Guardar cambios' : 'Crear destino' }}</button>
        </div>
    </div>
</form>
@endsection
