<?php

namespace App\Http\Controllers\Agencia;

use App\Http\Controllers\Controller;
use App\Support\Fotos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * «Mi perfil» de la agencia: logo, presentación, fotos, videos y datos de contacto.
 */
class PerfilAgenciaController extends Controller
{
    /** Máximo de fotos en la galería de una agencia. */
    public const MAX_FOTOS = 8;

    public function edit(Request $request)
    {
        return view('agencia.perfil', [
            'agencia' => $request->user(),
            'maxFotos' => self::MAX_FOTOS,
        ]);
    }

    public function update(Request $request)
    {
        $agencia = $request->user();
        // Cuántas fotos puede subir ahora: lo que falta para 8, y como mucho 3 por vez
        // (PHP en esta computadora acepta 8 MB por envío: 3 fotos de 2 MB caben bien)
        $espacioLibre = max(min(self::MAX_FOTOS - count($agencia->fotos ?? []), 3), 0);

        $datos = $request->validate([
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:100', Rule::unique('usuarios', 'correo')->ignore($agencia->id)],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'whatsapp' => ['required', 'string', 'regex:/^[0-9+\-\s()]{10,20}$/'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:1500'],
            // Fotos: máximo 2 MB cada una (es el límite de PHP en esta computadora)
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'quitar_logo' => ['nullable', 'boolean'],
            'fotos_nuevas' => ['nullable', 'array', 'max:'.$espacioLibre],
            'fotos_nuevas.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // Videos: un enlace por línea
            'videos' => ['nullable', 'string', 'max:2000'],
        ], [
            'fotos_nuevas.max' => $espacioLibre > 0
                ? 'Ahora puedes subir como máximo '.$espacioLibre.' '.($espacioLibre === 1 ? 'foto' : 'fotos').'.'
                : 'Ya tienes '.self::MAX_FOTOS.' fotos. Borra alguna para subir otras.',
        ]);

        // Videos: solo enlaces válidos de YouTube o Vimeo
        $videos = collect(preg_split('/\r\n|\r|\n/', $datos['videos'] ?? ''))
            ->map(fn ($v) => trim($v))->filter()->unique()->values();
        $invalidos = $videos->reject(fn ($v) => preg_match('~^https?://(www\.)?(youtube\.com|youtu\.be|vimeo\.com)/~i', $v));
        if ($invalidos->isNotEmpty()) {
            return back()->withInput()->withErrors(['videos' => 'Solo se aceptan enlaces de YouTube o Vimeo (uno por línea).']);
        }

        // Logo: quitar o reemplazar
        if ($request->boolean('quitar_logo')) {
            Fotos::borrar($agencia->logo);
            $agencia->logo = null;
        }
        if ($request->hasFile('logo')) {
            Fotos::borrar($agencia->logo);
            $agencia->logo = Fotos::guardar($request->file('logo'), 'logos');
        }

        // Fotos nuevas de la galería
        $fotos = $agencia->fotos ?? [];
        foreach ($request->file('fotos_nuevas', []) as $archivo) {
            $fotos[] = Fotos::guardar($archivo, 'agencias');
        }

        $agencia->fill([
            'nombre_comercial' => $datos['nombre_comercial'],
            'nombre' => $datos['nombre'],
            'correo' => strtolower($datos['correo']),
            'telefono' => $datos['telefono'] ?? null,
            'whatsapp' => preg_replace('/\D/', '', $datos['whatsapp']),
            'direccion' => $datos['direccion'] ?? null,
            'descripcion' => $datos['descripcion'] ?? null,
            'fotos' => array_values($fotos),
            'videos' => $videos->all(),
        ])->save();

        return redirect()->route('agencia.perfil')->with('exito', 'Tu perfil se guardó correctamente.');
    }

    /** Borra una foto de la galería (por su posición en la lista). */
    public function borrarFoto(Request $request, int $indice)
    {
        $agencia = $request->user();
        $fotos = $agencia->fotos ?? [];

        if (! array_key_exists($indice, $fotos)) {
            return back()->with('error', 'Esa foto ya no existe.');
        }

        Fotos::borrar($fotos[$indice]);
        unset($fotos[$indice]);
        $agencia->update(['fotos' => array_values($fotos)]);

        return back()->with('exito', 'La foto se borró.');
    }
}
