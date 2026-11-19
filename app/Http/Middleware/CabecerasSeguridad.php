<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Agrega «cabeceras de seguridad» a todas las páginas.
 * Son instrucciones para el navegador que protegen a los visitantes:
 * - X-Frame-Options: otra página no puede meter Explora RD dentro de un marco (evita engaños).
 * - X-Content-Type-Options: el navegador no «adivina» el tipo de los archivos.
 * - Referrer-Policy: no se envía la dirección completa a otras páginas.
 * - Permissions-Policy: la página no pide cámara, micrófono ni ubicación.
 */
class CabecerasSeguridad
{
    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        $respuesta->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $respuesta->headers->set('X-Content-Type-Options', 'nosniff');
        $respuesta->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $respuesta->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $respuesta;
    }
}
