@extends('errors.plantilla')

@section('codigo', '403')
@section('titulo', 'No tienes permiso')
@section('mensaje', 'No puedes ver o cambiar esta información porque no es de tu cuenta.')
@section('boton_extra')
    <a href="javascript:history.back()" class="boton secundario">Volver</a>
@endsection
