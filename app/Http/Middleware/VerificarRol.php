<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Revisa el rol del usuario antes de abrir un panel.
 *
 * Uso en las rutas:  ->middleware('rol:agencia')
 *                    ->middleware('rol:administrador')
 *                    ->middleware('rol:turista,agencia')   (varios roles)
 *
 * - Si no ha iniciado sesión, lo manda a la página de «Entrar».
 * - Si su rol no es el permitido, lo devuelve a su propio panel con un aviso.
 */
class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->guest(route('login'));
        }

        // Si el administrador la suspendió mientras tenía la sesión abierta, se cierra
        if ($usuario->activo === false) {
            auth()->logout();
            $request->session()->invalidate();

            return redirect()->route('login')->with('error', __('Tu cuenta está suspendida.'));
        }

        if (! in_array($usuario->rol, $roles, true)) {
            return redirect()
                ->route($usuario->rutaPanel())
                ->with('error', __('No tienes permiso para entrar a esa sección.'));
        }

        return $next($request);
    }
}
