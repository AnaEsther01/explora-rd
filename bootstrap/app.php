<?php

use App\Http\Middleware\CabecerasSeguridad;
use App\Http\Middleware\EstablecerIdioma;
use App\Http\Middleware\VerificarRol;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // «rol» es nuestro middleware que revisa el rol antes de abrir cada panel
        $middleware->alias([
            'rol' => VerificarRol::class,
        ]);

        // Cabeceras de seguridad e idioma elegido (español, inglés o francés) en todas las páginas
        $middleware->web(append: [CabecerasSeguridad::class, EstablecerIdioma::class]);

        // Quien no ha iniciado sesión y entra a una página privada va a «Entrar»
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Quien ya inició sesión y abre «Entrar» o «Registro» va a su propio panel
        $middleware->redirectUsersTo(fn (Request $request) => route($request->user()->rutaPanel()));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
