@extends('errors.plantilla')

@section('codigo', '413')
@section('titulo', 'Los archivos pesan demasiado')
@section('mensaje', 'Las fotos que elegiste pesan más de lo permitido. Sube como máximo 3 fotos a la vez, de 2 MB cada una.')
@section('boton_extra')
    <a href="javascript:history.back()" class="boton secundario">Volver</a>
@endsection
