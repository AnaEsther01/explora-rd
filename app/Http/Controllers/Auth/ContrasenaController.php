<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as ReglaContrasena;

/**
 * «¿Olvidaste tu contraseña?»: envía un enlace por correo y permite crear una nueva.
 * Mientras trabajamos en la computadora, el correo no se envía de verdad:
 * se guarda en storage/logs/laravel.log (MAIL_MAILER=log en el archivo .env).
 */
class ContrasenaController extends Controller
{
    /** Formulario para pedir el enlace. */
    public function solicitar()
    {
        return view('auth.olvide');
    }

    /** Envía el enlace para restablecer la contraseña. */
    public function enviarEnlace(Request $request)
    {
        $request->validate(['correo' => ['required', 'email']]);

        // Usamos «correo» porque así se llama la columna en la tabla usuarios
        Password::sendResetLink(['correo' => $request->input('correo')]);

        // Siempre el mismo mensaje, exista o no el correo (así nadie puede averiguar qué correos están registrados)
        return back()->with('exito', __('Si ese correo está registrado, te enviamos un enlace para crear una contraseña nueva.'));
    }

    /** Formulario para escribir la contraseña nueva (se llega desde el enlace del correo). */
    public function formulario(Request $request, string $token)
    {
        return view('auth.restablecer', [
            'token' => $token,
            'correo' => $request->query('email'),
        ]);
    }

    /** Guarda la contraseña nueva. */
    public function restablecer(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'correo' => ['required', 'email'],
            'contrasena' => ['required', 'confirmed', ReglaContrasena::min(8)->letters()->numbers()],
        ]);

        $estado = Password::reset(
            [
                'correo' => $request->input('correo'),
                'password' => $request->input('contrasena'),
                'password_confirmation' => $request->input('contrasena_confirmation'),
                'token' => $request->input('token'),
            ],
            function (Usuario $usuario, string $nueva) {
                $usuario->forceFill(['contrasena' => $nueva])->setRememberToken(null);
                $usuario->save();
            }
        );

        if ($estado === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('exito', __('Tu contraseña se cambió. Ya puedes iniciar sesión.'));
        }

        return back()->withInput($request->only('correo'))
            ->withErrors(['correo' => __('El enlace no es válido o ya venció. Pide uno nuevo.')]);
    }
}
