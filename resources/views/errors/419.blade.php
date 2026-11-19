@extends('errors.plantilla')

@section('codigo', '419')
@section('titulo', 'La página venció')
@section('mensaje', 'Pasó mucho tiempo desde que abriste el formulario. Recarga la página e inténtalo de nuevo.')
@section('boton_extra')
    <a href="javascript:history.back()" class="boton secundario">Volver</a>
@endsection
