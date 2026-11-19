<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Prueba de la página principal.
 * Usa una base de datos temporal en memoria (no toca explora_rd).
 */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true; // carga los datos de prueba antes de cada prueba

    public function test_la_pagina_principal_carga_con_destinos_y_excursiones(): void
    {
        $respuesta = $this->get('/');

        $respuesta->assertStatus(200);
        $respuesta->assertSee('Descubre República Dominicana');
        $respuesta->assertSee('Jarabacoa');                  // destino
        $respuesta->assertSee('Catamarán a isla Saona');     // excursión
        $respuesta->assertSee('Ruta de aventura por Jarabacoa'); // circuito
        $respuesta->assertSee('https://wa.me/', false);      // botón de WhatsApp
    }
}
