<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Cuando una parte de la página pide «el asistente turístico», recibe el que usa Claude.
        // (En las pruebas automáticas se cambia por uno falso que no gasta dinero.)
        $this->app->bind(\App\Contracts\AsistenteTuristico::class, \App\Support\Asistente\AsistenteClaude::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Los botones de página (1, 2, 3...) usan el estilo de Bootstrap 5
        Paginator::useBootstrapFive();

        // Fechas en español (ej.: «jueves 20 de diciembre de 2026»)
        Carbon::setLocale(config('app.locale'));
    }
}
