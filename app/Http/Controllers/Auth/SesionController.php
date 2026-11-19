<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Inicio y cierre de sesión.
 */
class SesionController extends Controller
{
    /**
     * Muestra la página «Iniciar sesión».
     * Con ?volver=/excursiones/... regresa a esa página después de entrar
     * (por ejemplo, cuando un turista quiere reservar y todavía no ha iniciado sesión).
     */
    public function create(Request $request)
    {
        $volver = (string) $request->query('volver');

        // Solo direcciones de nuestra propia página (empiezan con «/», pero no con «//»)
        if (str_starts_with($volver, '/') && ! str_starts_with($volver, '//')) {
            $request->session()->put('url.intended', url($volver));
        }

        return view('auth.login');
    }

    /** Revisa el correo y la contraseña. Si son correctos, abre la sesión. */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'correo' => ['required', 'email'],
            'contrasena' => ['required', 'string'],
        ]);

        // Laravel compara la contraseña escrita con la cifrada en la base de datos.
        // (La clave se llama «password» aquí, pero el modelo sabe que la columna es «contrasena»).
        $correcto = Auth::attempt(
            ['correo' => $datos['correo'], 'password' => $datos['contrasena']],
            $request->boolean('recordarme')
        );

        if (! $correcto) {
            // Mensaje general: no decimos si falló el correo o la contraseña (más seguro)
            throw ValidationException::withMessages([
                'correo' => __('El correo o la contraseña no son correctos.'),
            ]);
        }

        $usuario = $request->user();

        // Cuenta suspendida por el administrador: no puede entrar
        if (! $usuario->activo) {
            Auth::logout();
            throw ValidationException::withMessages([
                'correo' => __('Tu cuenta está suspendida. Escríbenos a :correo para más información.', ['correo' => config('explora.correo')]),
            ]);
        }

        // Nueva sesión para evitar que alguien reutilice una sesión vieja
        $request->session()->regenerate();

        // Si intentaba abrir una página privada, vuelve ahí; si no, va a su panel
        return redirect()->intended(route($usuario->rutaPanel()))
            ->with('exito', __('¡Hola, :nombre! Iniciaste sesión correctamente.', ['nombre' => $usuario->nombreVisible()]));
    }

    /** Cierra la sesión. */
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio')->with('exito', __('Cerraste sesión. ¡Vuelve pronto!'));
    }
}
