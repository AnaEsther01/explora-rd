@extends('layouts.admin')

@php
    $editando = $usuario->exists;
    $rolActual = old('rol', $usuario->rol);
@endphp

@section('titulo', ($editando ? 'Editar a '.$usuario->nombreVisible() : 'Nuevo usuario').' — Panel del administrador | Explora RD')
@section('seccion', 'usuarios')

@section('panel')
<form method="POST" action="{{ $editando ? route('admin.usuarios.update', $usuario) : route('admin.usuarios.store') }}" novalidate>
    @csrf
    @if ($editando) @method('PUT') @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="subtitulo-lora mb-0">{{ $editando ? 'Editar: '.$usuario->nombreVisible() : 'Crear una cuenta' }}</h2>
        <a href="{{ route('admin.usuarios.index') }}" class="enlace-suave"><i class="bi bi-arrow-left"></i> Volver a la lista</a>
    </div>

    @if ($errors->any())
        <div class="alert alerta alerta-error mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i><span>Revisa los campos marcados en rojo.</span>
        </div>
    @endif

    <div class="caja-panel mb-4">
        <h3 class="caja-lateral-titulo">Cuenta</h3>
        <div class="row g-3">
            <div class="col-md-4">
                <label for="rolAdmin" class="form-label">Rol</label>
                <select id="rolAdmin" name="rol" class="form-select @error('rol') is-invalid @enderror">
                    @foreach ($roles as $clave => $texto)
                        <option value="{{ $clave }}" @selected($rolActual === $clave)>{{ $texto }}</option>
                    @endforeach
                </select>
                @error('rol') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-8">
                <label for="nombre" class="form-label">Nombre de la persona</label>
                <input type="text" id="nombre" name="nombre" maxlength="100" value="{{ old('nombre', $usuario->nombre) }}"
                       class="form-control @error('nombre') is-invalid @enderror">
                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-7">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input type="email" id="correo" name="correo" maxlength="100" value="{{ old('correo', $usuario->correo) }}"
                       class="form-control @error('correo') is-invalid @enderror">
                @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-5">
                <label for="telefono" class="form-label">Teléfono <span class="opcional">(opcional)</span></label>
                <input type="tel" id="telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}"
                       class="form-control @error('telefono') is-invalid @enderror">
                @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    {{-- Datos de agencia: solo se muestran si el rol es «Agencia» --}}
    <div class="caja-panel mb-4 campos-agencia-admin {{ $rolActual === 'agencia' ? '' : 'd-none' }}">
        <h3 class="caja-lateral-titulo">Datos de la agencia</h3>
        <div class="row g-3">
            <div class="col-md-7">
                <label for="nombre_comercial" class="form-label">Nombre de la agencia</label>
                <input type="text" id="nombre_comercial" name="nombre_comercial" maxlength="150" value="{{ old('nombre_comercial', $usuario->nombre_comercial) }}"
                       class="form-control @error('nombre_comercial') is-invalid @enderror">
                @error('nombre_comercial') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-5">
                <label for="whatsapp" class="form-label">WhatsApp</label>
                <input type="tel" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $usuario->whatsapp) }}"
                       class="form-control @error('whatsapp') is-invalid @enderror" placeholder="1 809 555 0000">
                @error('whatsapp') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label for="direccion" class="form-label">Dirección <span class="opcional">(opcional)</span></label>
                <input type="text" id="direccion" name="direccion" maxlength="200" value="{{ old('direccion', $usuario->direccion) }}"
                       class="form-control @error('direccion') is-invalid @enderror">
                @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label for="descripcion" class="form-label">Presentación <span class="opcional">(opcional)</span></label>
                <textarea id="descripcion" name="descripcion" rows="3" maxlength="1500"
                          class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $usuario->descripcion) }}</textarea>
                @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <p class="small text-muted mb-0"><i class="bi bi-info-circle"></i> El logo, las fotos y los videos los sube la propia agencia desde su panel.</p>
        </div>
    </div>

    <div class="caja-panel mb-4">
        <h3 class="caja-lateral-titulo">{{ $editando ? 'Cambiar contraseña' : 'Contraseña' }}</h3>
        @if ($editando)
            <p class="small text-muted">Déjala en blanco para no cambiarla.</p>
        @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label for="contrasena" class="form-label">{{ $editando ? 'Contraseña nueva' : 'Contraseña' }}</label>
                <div class="campo-contrasena">
                    <input type="password" id="contrasena" name="contrasena" autocomplete="new-password"
                           class="form-control @error('contrasena') is-invalid @enderror">
                    <button type="button" class="ver-contrasena" data-objetivo="contrasena" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                    @error('contrasena') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <label for="contrasena_confirmation" class="form-label">Repite la contraseña</label>
                <input type="password" id="contrasena_confirmation" name="contrasena_confirmation" autocomplete="new-password" class="form-control">
            </div>
            <div class="col-12"><small class="text-muted"><i class="bi bi-shield-lock"></i> Mínimo 8 caracteres, con letras y números.</small></div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-contorno">Cancelar</a>
        <button type="submit" class="btn btn-turquesa"><i class="bi bi-check-lg"></i> {{ $editando ? 'Guardar cambios' : 'Crear cuenta' }}</button>
    </div>
</form>
@endsection
