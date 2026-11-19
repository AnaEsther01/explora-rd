<?php

namespace App\Http\Controllers;

use App\Models\Destino;
use App\Models\Excursion;

/**
 * Controlador de la página principal.
 * Busca en la base de datos lo que se muestra en la portada.
 */
class InicioController extends Controller
{
    public function index()
    {
        // Destinos destacados (los 8 primeros, en dos filas)
        $destinos = Destino::orderBy('id')->take(8)->get();

        // Fotos para la galería: destinos que ya tienen foto guardada en public/img
        $galeria = Destino::whereNotNull('foto')->orderBy('id')->get()
            ->filter(fn ($d) => file_exists(public_path($d->foto)))
            ->skip(6)->take(6)->values();

        // Excursiones destacadas (solo las disponibles)
        $excursiones = Excursion::with(['agencia', 'destino'])
            ->publicas()
            ->conValoracion() // promedio de estrellas para las tarjetas
            ->where('tipo', 'excursion')
            ->latest('id')
            ->take(6)
            ->get();

        // Circuitos y paquetes
        $circuitos = Excursion::with(['agencia', 'destino'])
            ->publicas()
            ->conValoracion() // promedio de estrellas para las tarjetas
            ->whereIn('tipo', ['circuito', 'paquete'])
            ->take(3)
            ->get();

        // Lista de todos los destinos para el buscador
        $listaDestinos = Destino::orderBy('nombre')->pluck('nombre');

        return view('inicio', compact('destinos', 'excursiones', 'circuitos', 'listaDestinos', 'galeria'));
    }
}
