<?php

namespace App\Http\Controllers\Agencia;

use App\Http\Controllers\Controller;
use App\Models\Destino;
use App\Models\Excursion;
use App\Support\Fotos;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * «Mis excursiones» y «Paquetes»: la agencia publica, edita y elimina sus experiencias.
 * Una agencia SOLO puede ver y cambiar sus propias excursiones.
 */
class ExcursionAgenciaController extends Controller
{
    /** Lista. Con ?ver=paquetes muestra circuitos y paquetes; si no, las excursiones. */
    public function index(Request $request)
    {
        $verPaquetes = $request->query('ver') === 'paquetes';
        $buscar = trim((string) $request->query('buscar'));

        $excursiones = $request->user()->excursiones()
            ->with('destino')
            ->withCount('reservas')
            ->when($verPaquetes, fn ($q) => $q->whereIn('tipo', ['circuito', 'paquete']), fn ($q) => $q->where('tipo', 'excursion'))
            ->when($buscar !== '', fn ($q) => $q->where('titulo', 'like', "%{$buscar}%"))
            ->orderByDesc('disponible')
            ->orderBy('titulo')
            ->get();

        return view('agencia.excursiones.index', compact('excursiones', 'verPaquetes', 'buscar'));
    }

    public function create(Request $request)
    {
        $excursion = new Excursion([
            'tipo' => $request->query('tipo', 'excursion'),
            'disponible' => true,
        ]);

        return view('agencia.excursiones.form', [
            'excursion' => $excursion,
            'destinos' => Destino::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        $excursion = new Excursion($datos);
        $excursion->agencia_id = $request->user()->id; // siempre la agencia que inició sesión
        $excursion->disponible = $request->boolean('disponible');

        if ($request->hasFile('foto')) {
            $excursion->foto = Fotos::guardar($request->file('foto'), 'excursiones');
        }

        $excursion->save();

        return redirect()->route('agencia.excursiones.index', $this->filtroLista($excursion))
            ->with('exito', '«'.$excursion->titulo.'» se publicó correctamente.');
    }

    public function edit(Request $request, Excursion $excursion)
    {
        $this->asegurarDuena($request, $excursion);

        return view('agencia.excursiones.form', [
            'excursion' => $excursion,
            'destinos' => Destino::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Excursion $excursion)
    {
        $this->asegurarDuena($request, $excursion);

        $datos = $this->validar($request);
        $excursion->fill($datos);
        $excursion->disponible = $request->boolean('disponible');

        if ($request->boolean('quitar_foto')) {
            Fotos::borrar($excursion->foto);
            $excursion->foto = null;
        }
        if ($request->hasFile('foto')) {
            Fotos::borrar($excursion->foto);
            $excursion->foto = Fotos::guardar($request->file('foto'), 'excursiones');
        }

        $excursion->save();

        return redirect()->route('agencia.excursiones.index', $this->filtroLista($excursion))
            ->with('exito', 'Los cambios de «'.$excursion->titulo.'» se guardaron.');
    }

    /** Publicar / ocultar con un clic. */
    public function cambiarDisponible(Request $request, Excursion $excursion)
    {
        $this->asegurarDuena($request, $excursion);

        $excursion->update(['disponible' => ! $excursion->disponible]);

        return back()->with('exito', $excursion->disponible
            ? '«'.$excursion->titulo.'» ahora está visible para los turistas.'
            : '«'.$excursion->titulo.'» quedó oculta. Los turistas ya no la ven.');
    }

    public function destroy(Request $request, Excursion $excursion)
    {
        $this->asegurarDuena($request, $excursion);

        // Si ya tiene reservas no se borra (se perderían las reservas de los turistas)
        if ($excursion->reservas()->exists()) {
            return back()->with('error', 'No se puede eliminar «'.$excursion->titulo.'» porque tiene reservas. Puedes ocultarla para que no se muestre.');
        }

        Fotos::borrar($excursion->foto);
        $titulo = $excursion->titulo;
        $filtro = $this->filtroLista($excursion);
        $excursion->delete();

        return redirect()->route('agencia.excursiones.index', $filtro)
            ->with('exito', '«'.$titulo.'» se eliminó.');
    }

    // ---------- Ayudas ----------

    /** Si la excursión no es de esta agencia, no la deja verla ni cambiarla (error 403). */
    private function asegurarDuena(Request $request, Excursion $excursion): void
    {
        abort_unless((int) $excursion->agencia_id === (int) $request->user()->id, 403, 'Esta excursión no es de tu agencia.');
    }

    /**
     * Reglas que deben cumplir los datos del formulario.
     * Devuelve solo los datos de texto: la foto y las casillas se manejan aparte.
     */
    private function validar(Request $request): array
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'tipo' => ['required', Rule::in(array_keys(Excursion::TIPOS))],
            'categoria' => ['nullable', Rule::in(array_keys(Excursion::CATEGORIAS))],
            'destino_id' => ['required', 'exists:destinos,id'],
            'descripcion' => ['required', 'string', 'max:3000'],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'duracion' => ['nullable', 'string', 'max:50'],
            'ubicacion' => ['nullable', 'string', 'max:150'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'disponible' => ['nullable', 'boolean'],
            'quitar_foto' => ['nullable', 'boolean'],
        ]);

        return Arr::except($datos, ['foto', 'disponible', 'quitar_foto']);
    }

    /** Para volver a la pestaña correcta (excursiones o paquetes) después de guardar. */
    private function filtroLista(Excursion $excursion): array
    {
        return $excursion->tipo === 'excursion' ? [] : ['ver' => 'paquetes'];
    }
}
