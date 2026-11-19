<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Elige el idioma de la página en cada visita.
 * El idioma se guarda en la sesión cuando la persona lo elige en el menú (ES / EN / FR).
 * Si nunca eligió, se usa el español.
 */
class EstablecerIdioma
{
    /** Idiomas disponibles: código => nombre y bandera */
    public const IDIOMAS = [
        'es' => ['nombre' => 'Español', 'bandera' => '🇩🇴'],
        'en' => ['nombre' => 'English', 'bandera' => '🇺🇸'],
        'fr' => ['nombre' => 'Français', 'bandera' => '🇫🇷'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $idioma = $request->session()->get('idioma', config('app.locale'));

        if (! array_key_exists($idioma, self::IDIOMAS)) {
            $idioma = 'es';
        }

        App::setLocale($idioma);
        Carbon::setLocale($idioma); // fechas: «jueves» / «Thursday» / «jeudi»

        return $next($request);
    }
}
