<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Usuario;
use App\Support\Fotos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * El administrador gestiona las cuentas: turistas, agencias y otros administradores.
 * Puede verlas, crearlas, editarlas, suspenderlas y borrarlas.
 */
class UsuarioAdminController extends Controller
{
    public const ROLES = ['turista' => 'Turista', 'agencia' => 'Agencia', 'administrador' => 'Administrador'];

    public function index(Request $request)
    {
        $rol = array_key_exists($request->query('rol'), self::ROLES) ? $request->query('rol') : null;
        $estado = in_array($request->query('estado'), ['activos', 'suspendidos'], true) ? $request->query('estado') : null;
        $buscar = trim((string) $request->query('buscar'));

        $usuarios = Usuario::query()
            ->withCount(['excursiones', 'reservas'])
            ->when($rol, fn ($q) => $q->where('rol', $rol))
            ->when($estado === 'activos', fn ($q) => $q->where('activo', true))
            ->when($estado === 'suspendidos', fn ($q) => $q->where('activo', false))
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$buscar}%")
                ->orWhere('correo', 'like', "%{$buscar}%")
                ->orWhere('nombre_comercial', 'like', "%{$buscar}%")))
            ->orderBy('rol')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $conteo = Usuario::selectRaw('rol, count(*) as total')->groupBy('rol')->pluck('total', 'rol');

        return view('admin.usuarios.index', compact('usuarios', 'rol', 'estado', 'buscar', 'conteo'));
    }

    public function create(Request $request)
    {
        $usuario = new Usuario([
            'rol' => array_key_exists($request->query('rol'), self::ROLES) ? $request->query('rol') : 'turista',
        ]);

        return view('admin.usuarios.form', ['usuario' => $usuario, 'roles' => self::ROLES]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        $usuario = new Usuario($this->datosParaGuardar($datos));
        $usuario->contrasena = $datos['contrasena'];
        $usuario->save();

        return redirect()->route('admin.usuarios.index')
            ->with('exito', 'Se creó la cuenta de '.$usuario->nombreVisible().'.');
    }

    public function edit(Usuario $usuario)
    {
        return view('admin.usuarios.form', ['usuario' => $usuario, 'roles' => self::ROLES]);
    }

    public function update(Request $request, Usuario $usuario)
    {
        $datos = $this->validar($request, $usuario);

        // Un administrador no puede quitarse a sí mismo el rol (se quedaría fuera del panel)
        if ($usuario->id === $request->user()->id && $datos['rol'] !== 'administrador') {
            return back()->withInput()->with('error', 'No puedes quitarte tu propio rol de administrador.');
        }

        // Una agencia con excursiones no puede dejar de ser agencia (sus excursiones quedarían sin dueño)
        if ($usuario->esAgencia() && $datos['rol'] !== 'agencia' && $usuario->excursiones()->exists()) {
            return back()->withInput()->with('error', 'Esta agencia tiene excursiones publicadas. Bórralas o suspende la cuenta en vez de cambiarle el rol.');
        }

        $usuario->fill($this->datosParaGuardar($datos));
        if (! empty($datos['contrasena'])) {
            $usuario->contrasena = $datos['contrasena'];
        }
        $usuario->save();

        return redirect()->route('admin.usuarios.index')
            ->with('exito', 'Se guardaron los cambios de '.$usuario->nombreVisible().'.');
    }

    /** Suspender o reactivar una cuenta. */
    public function cambiarActivo(Request $request, Usuario $usuario)
    {
        if ($usuario->id === $request->user()->id) {
            return back()->with('error', 'No puedes suspender tu propia cuenta.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        return back()->with('exito', $usuario->activo
            ? 'Se reactivó la cuenta de '.$usuario->nombreVisible().'.'
            : 'Se suspendió la cuenta de '.$usuario->nombreVisible().'. Ya no puede entrar'.($usuario->esAgencia() ? ' y sus excursiones no se muestran.' : '.'));
    }

    /** Borrar una cuenta (solo si no tiene reservas que se perderían). */
    public function destroy(Request $request, Usuario $usuario)
    {
        if ($usuario->id === $request->user()->id) {
            return back()->with('error', 'No puedes borrar tu propia cuenta.');
        }

        // Reservas que se perderían: las que hizo como turista o las que recibió como agencia
        $tieneReservas = $usuario->reservas()->exists()
            || Reserva::whereHas('excursion', fn ($q) => $q->where('agencia_id', $usuario->id))->exists();

        if ($tieneReservas) {
            return back()->with('error', 'No se puede borrar a '.$usuario->nombreVisible().' porque tiene reservas. Puedes suspender la cuenta.');
        }

        // Borrar también sus fotos subidas (logo, galería y fotos de sus excursiones)
        Fotos::borrar($usuario->logo);
        foreach ($usuario->fotos ?? [] as $foto) {
            Fotos::borrar($foto);
        }
        foreach ($usuario->excursiones as $excursion) {
            Fotos::borrar($excursion->foto);
        }

        $nombre = $usuario->nombreVisible();
        $usuario->delete(); // sus excursiones se borran solas (clave foránea en cascada)

        return redirect()->route('admin.usuarios.index')->with('exito', 'Se borró la cuenta de '.$nombre.'.');
    }

    // ---------- Ayudas ----------

    private function validar(Request $request, ?Usuario $usuario = null): array
    {
        $esAgencia = $request->input('rol') === 'agencia';

        return $request->validate([
            'rol' => ['required', Rule::in(array_keys(self::ROLES))],
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:100', Rule::unique('usuarios', 'correo')->ignore($usuario?->id)],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            // Al crear, la contraseña es obligatoria; al editar, solo si se quiere cambiar
            'contrasena' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
            'nombre_comercial' => [$esAgencia ? 'required' : 'nullable', 'string', 'max:150'],
            'whatsapp' => [$esAgencia ? 'required' : 'nullable', 'string', 'regex:/^[0-9+\-\s()]{10,20}$/'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:1500'],
        ]);
    }

    private function datosParaGuardar(array $datos): array
    {
        $esAgencia = $datos['rol'] === 'agencia';

        return [
            'rol' => $datos['rol'],
            'nombre' => $datos['nombre'],
            'correo' => strtolower($datos['correo']),
            'telefono' => $datos['telefono'] ?? null,
            'nombre_comercial' => $esAgencia ? $datos['nombre_comercial'] : null,
            'whatsapp' => $esAgencia ? preg_replace('/\D/', '', $datos['whatsapp']) : null,
            'direccion' => $esAgencia ? ($datos['direccion'] ?? null) : null,
            'descripcion' => $esAgencia ? ($datos['descripcion'] ?? null) : null,
        ];
    }
}
