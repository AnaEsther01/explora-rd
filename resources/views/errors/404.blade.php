@extends('errors.plantilla')

@section('codigo', '404')
@section('titulo', 'Página no encontrada')
@section('mensaje', 'La página que buscas no existe o cambió de dirección. Quizás quieras explorar nuestros destinos.')
@section('boton_extra')
    <a href="/destinos" class="boton secundario">Ver destinos</a>
@endsection
