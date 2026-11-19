<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Registro de cuentas nuevas: turista o agencia.
 * (Los administradores no se registran aquí; se crean desde la base de datos.)
 */
class RegistroController extends Controller
{
    /** Muestra el formulario. Con ?tipo=agencia se abre en la pestaña de agencias. */
    public function create(Request $request)
    {
        $tipo = $request->query('tipo') === 'agencia' ? 'agencia' : 'turista';

        return view('auth.registro', compact('tipo'));
    }

    /** Guarda la cuenta nueva y abre la sesión. */
    public function store(Request $request)
    {
        $esAgencia = $request->input('rol') === 'agencia';

        $reglas = [
            'rol' => ['required', Rule::in(['turista', 'agencia'])],
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:100', 'unique:usuarios,correo'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'contrasena' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];

        // Datos que solo piden las agencias
        if ($esAgencia) {
            $reglas += [
                'nombre_comercial' => ['required', 'string', 'max:150'],
                'whatsapp' => ['required', 'string', 'regex:/^[0-9+\-\s()]{10,20}$/'],
                'direccion' => ['nullable', 'string', 'max:200'],
                'descripcion' => ['nullable', 'string', 'max:1000'],
            ];
        }

        $datos = $request->validate($reglas);

        $usuario = Usuario::create([
            'rol' => $datos['rol'],
            'nombre' => $datos['nombre'],
            'correo' => strtolower($datos['correo']),
            'telefono' => $datos['telefono'] ?? null,
            'contrasena' => $datos['contrasena'], // el modelo la cifra sola
            'nombre_comercial' => $esAgencia ? $datos['nombre_comercial'] : null,
            // WhatsApp se guarda solo con números (así lo necesita wa.me)
            'whatsapp' => $esAgencia ? preg_replace('/\D/', '', $datos['whatsapp']) : null,
            'direccion' => $esAgencia ? ($datos['direccion'] ?? null) : null,
            'descripcion' => $esAgencia ? ($datos['descripcion'] ?? null) : null,
        ]);

        Auth::login($usuario);
        $request->session()->regenerate();

        $mensaje = $esAgencia
            ? __('¡Te damos la bienvenida a Explora RD! :nombre ya tiene su cuenta de agencia.', ['nombre' => $usuario->nombreVisible()])
            : __('¡Te damos la bienvenida a Explora RD, :nombre! Tu cuenta está lista.', ['nombre' => $usuario->nombre]);

        return redirect()->route($usuario->rutaPanel())->with('exito', $mensaje);
    }
}
