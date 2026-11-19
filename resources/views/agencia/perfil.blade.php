@extends('layouts.agencia')

@section('titulo', 'Mi perfil — Panel de la agencia | Explora RD')
@section('seccion', 'perfil')

@section('panel')
<form method="POST" action="{{ route('agencia.perfil.update') }}" enctype="multipart/form-data" novalidate>
    @csrf
    @method('PUT')

    {{-- Si hubo errores, un resumen arriba --}}
    @if ($errors->any())
        <div class="alert alerta alerta-error mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Revisa los campos marcados en rojo.</span>
        </div>
    @endif

    {{-- ===== Logo y presentación ===== --}}
    <div class="caja-panel mb-4">
        <h2 class="caja-lateral-titulo">Logotipo y presentación</h2>

        <div class="d-flex flex-wrap align-items-center gap-4 mb-4">
            <div class="logo-editor">
                @if ($agencia->tieneLogo())
                    <img src="{{ asset($agencia->logo) }}" alt="Logo actual" id="vistaLogo" class="logo-editor-img">
                @else
                    <span class="logo-editor-img agencia-iniciales" id="vistaLogoIniciales">{{ $agencia->iniciales() }}</span>
                    <img src="" alt="Vista previa del logo" id="vistaLogo" class="logo-editor-img d-none">
                @endif
            </div>
            <div class="flex-grow-1">
                <label for="logo" class="form-label">Logotipo</label>
                <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/webp"
                       class="form-control @error('logo') is-invalid @enderror" data-vista="vistaLogo">
                @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="text-muted">JPG, PNG o WEBP. Máximo 2 MB. Mejor si es cuadrado.</small>
                @if ($agencia->logo)
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="quitar_logo" name="quitar_logo" value="1">
                        <label class="form-check-label small" for="quitar_logo">Quitar el logo actual</label>
                    </div>
                @endif
            </div>
        </div>

        <div class="mb-3">
            <label for="nombre_comercial" class="form-label">Nombre de la agencia</label>
            <input type="text" id="nombre_comercial" name="nombre_comercial" maxlength="150"
                   value="{{ old('nombre_comercial', $agencia->nombre_comercial) }}"
                   class="form-control @error('nombre_comercial') is-invalid @enderror">
            @error('nombre_comercial') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="descripcion" class="form-label">Presentación</label>
            <textarea id="descripcion" name="descripcion" rows="5" maxlength="1500" data-contador="contadorDescripcion"
                      class="form-control @error('descripcion') is-invalid @enderror"
                      placeholder="Cuéntales a los turistas quiénes son, qué experiencias ofrecen y por qué elegirlos.">{{ old('descripcion', $agencia->descripcion) }}</textarea>
            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <small class="text-muted"><span id="contadorDescripcion">0</span> / 1500 caracteres</small>
        </div>
    </div>

    {{-- ===== Contacto ===== --}}
    <div class="caja-panel mb-4">
        <h2 class="caja-lateral-titulo">Información de contacto</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre del responsable</label>
                <input type="text" id="nombre" name="nombre" maxlength="100" value="{{ old('nombre', $agencia->nombre) }}"
                       class="form-control @error('nombre') is-invalid @enderror">
                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input type="email" id="correo" name="correo" maxlength="100" value="{{ old('correo', $agencia->correo) }}"
                       class="form-control @error('correo') is-invalid @enderror">
                @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="whatsapp" class="form-label">WhatsApp <small class="text-muted">(con código de país)</small></label>
                <input type="tel" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $agencia->whatsapp) }}"
                       class="form-control @error('whatsapp') is-invalid @enderror" placeholder="1 809 555 0000">
                @error('whatsapp') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="telefono" class="form-label">Teléfono <span class="opcional">(opcional)</span></label>
                <input type="tel" id="telefono" name="telefono" value="{{ old('telefono', $agencia->telefono) }}"
                       class="form-control @error('telefono') is-invalid @enderror">
                @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label for="direccion" class="form-label">Dirección <span class="opcional">(opcional)</span></label>
                <input type="text" id="direccion" name="direccion" maxlength="200" value="{{ old('direccion', $agencia->direccion) }}"
                       class="form-control @error('direccion') is-invalid @enderror">
                @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    {{-- ===== Fotos ===== --}}
    <div class="caja-panel mb-4">
        <div class="d-flex justify-content-between align-items-baseline">
            <h2 class="caja-lateral-titulo">Fotografías</h2>
            <small class="text-muted">{{ count($agencia->fotos ?? []) }} de {{ $maxFotos }}</small>
        </div>

        @if (! empty($agencia->fotos))
            <div class="galeria-editor mb-3">
                @foreach ($agencia->fotos as $i => $foto)
                    <div class="galeria-editor-item">
                        @if (file_exists(public_path($foto)))
                            <img src="{{ asset($foto) }}" alt="Foto {{ $i + 1 }} de la agencia">
                        @else
                            <div class="galeria-editor-falta"><i class="bi bi-image"></i></div>
                        @endif
                        {{-- El botón usa el formulario de abajo (no se puede poner un formulario dentro de otro) --}}
                        <button type="submit" form="borrarFoto{{ $i }}" class="galeria-editor-borrar" title="Borrar foto"
                                onclick="return confirm('¿Borrar esta foto?')"><i class="bi bi-trash"></i></button>
                    </div>
                @endforeach
            </div>
        @endif

        @if (count($agencia->fotos ?? []) < $maxFotos)
            <label for="fotos_nuevas" class="form-label">Agregar fotos</label>
            <input type="file" id="fotos_nuevas" name="fotos_nuevas[]" multiple accept="image/jpeg,image/png,image/webp"
                   class="form-control @error('fotos_nuevas') is-invalid @enderror @error('fotos_nuevas.*') is-invalid @enderror">
            @error('fotos_nuevas') <div class="invalid-feedback">{{ $message }}</div> @enderror
            @error('fotos_nuevas.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <small class="text-muted">Puedes elegir varias a la vez (hasta 3 por vez). Máximo 2 MB cada una. Usa fotos propias o con permiso.</small>
        @endif
    </div>

    {{-- ===== Videos ===== --}}
    <div class="caja-panel mb-4">
        <h2 class="caja-lateral-titulo">Videos</h2>
        <label for="videos" class="form-label">Enlaces de YouTube o Vimeo <small class="text-muted">(uno por línea)</small></label>
        <textarea id="videos" name="videos" rows="3" class="form-control @error('videos') is-invalid @enderror"
                  placeholder="https://www.youtube.com/watch?v=...">{{ old('videos', implode("\n", $agencia->videos ?? [])) }}</textarea>
        @error('videos') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <small class="text-muted">Los videos se verán en tu perfil público.</small>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('agencia.panel') }}" class="btn btn-contorno">Cancelar</a>
        <button type="submit" class="btn btn-turquesa"><i class="bi bi-check-lg"></i> Guardar cambios</button>
    </div>
</form>

{{-- Formularios ocultos para borrar cada foto --}}
@foreach ($agencia->fotos ?? [] as $i => $foto)
    <form id="borrarFoto{{ $i }}" method="POST" action="{{ route('agencia.perfil.foto.borrar', $i) }}" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endforeach
@endsection
