<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destino;
use App\Support\Fotos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * El administrador gestiona los destinos: crear, editar (foto, descripción, actividades) y borrar.
 */
class DestinoAdminController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar'));

        $destinos = Destino::withCount('excursiones')
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$buscar}%")->orWhere('provincia', 'like', "%{$buscar}%")))
            ->orderBy('nombre')
            ->get();

        return view('admin.destinos.index', compact('destinos', 'buscar'));
    }

    public function create()
    {
        return view('admin.destinos.form', ['destino' => new Destino(['tipo' => 'playa'])]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        $destino = new Destino($datos);
        if ($request->hasFile('foto')) {
            $destino->foto = Fotos::guardar($request->file('foto'), 'destinos');
        }
        $destino->save();

        return redirect()->route('admin.destinos.index')->with('exito', 'Se creó el destino '.$destino->nombre.'.');
    }

    public function edit(Destino $destino)
    {
        return view('admin.destinos.form', compact('destino'));
    }

    public function update(Request $request, Destino $destino)
    {
        $datos = $this->validar($request, $destino);
        $destino->fill($datos);

        if ($request->boolean('quitar_foto')) {
            Fotos::borrar($destino->foto);
            $destino->foto = null;
        }
        if ($request->hasFile('foto')) {
            Fotos::borrar($destino->foto);
            $destino->foto = Fotos::guardar($request->file('foto'), 'destinos');
        }
        $destino->save();

        return redirect()->route('admin.destinos.index')->with('exito', 'Se guardaron los cambios de '.$destino->nombre.'.');
    }

    public function destroy(Destino $destino)
    {
        // La base de datos no deja borrar un destino que tiene excursiones (se quedarían sin destino)
        if ($destino->excursiones()->exists()) {
            return back()->with('error', 'No se puede borrar '.$destino->nombre.' porque tiene excursiones. Primero hay que moverlas o borrarlas.');
        }

        Fotos::borrar($destino->foto);
        $nombre = $destino->nombre;
        $destino->delete();

        return redirect()->route('admin.destinos.index')->with('exito', 'Se borró el destino '.$nombre.'.');
    }

    /** Revisa los datos. Las actividades se escriben una por línea. */
    private function validar(Request $request, ?Destino $destino = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('destinos', 'nombre')->ignore($destino?->id)],
            'provincia' => ['required', 'string', 'max:100'],
            'tipo' => ['required', Rule::in(array_keys(Destino::TIPOS))],
            // Ubicación para el mapa (opcional). República Dominicana está entre
            // latitud 17.3 y 20.1, y longitud -72.1 y -68.2: así se evitan errores de escritura.
            'latitud' => ['nullable', 'required_with:longitud', 'numeric', 'between:17.3,20.1'],
            'longitud' => ['nullable', 'required_with:latitud', 'numeric', 'between:-72.1,-68.2'],
            'descripcion' => ['required', 'string', 'max:3000'],
            'actividades' => ['nullable', 'string', 'max:2000'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'quitar_foto' => ['nullable', 'boolean'],
            // Traducciones opcionales (inglés y francés)
            'traducciones' => ['nullable', 'array'],
            'traducciones.*.descripcion' => ['nullable', 'string', 'max:3000'],
            'traducciones.*.actividades' => ['nullable', 'string', 'max:2000'],
        ], [
            'nombre.unique' => 'Ya existe un destino con ese nombre.',
            'latitud.between' => 'La latitud debe estar entre 17.3 y 20.1 (República Dominicana).',
            'longitud.between' => 'La longitud debe estar entre -72.1 y -68.2 (República Dominicana). Recuerda el signo menos.',
            'latitud.required_with' => 'Escribe también la latitud.',
            'longitud.required_with' => 'Escribe también la longitud.',
        ]);

        // Convierte «una actividad por línea» en una lista
        $aLista = fn (?string $texto) => collect(preg_split('/\r\n|\r|\n/', $texto ?? ''))
            ->map(fn ($a) => trim($a))->filter()->values()->all();

        $datos['actividades'] = $aLista($datos['actividades'] ?? '');

        // Solo se guardan los idiomas que tienen algo escrito
        $traducciones = [];
        foreach (array_keys(Destino::IDIOMAS_TRADUCCION) as $idioma) {
            $descripcion = trim($datos['traducciones'][$idioma]['descripcion'] ?? '');
            $actividades = $aLista($datos['traducciones'][$idioma]['actividades'] ?? '');
            if ($descripcion !== '' || $actividades) {
                $traducciones[$idioma] = array_filter(['descripcion' => $descripcion ?: null, 'actividades' => $actividades ?: null]);
            }
        }
        $datos['traducciones'] = $traducciones ?: null;

        unset($datos['foto'], $datos['quitar_foto']);

        return $datos;
    }
}
