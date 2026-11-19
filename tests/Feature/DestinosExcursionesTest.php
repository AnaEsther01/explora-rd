<?php

namespace Tests\Feature;

use App\Models\Excursion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de la Etapa 4: páginas de destinos y catálogo de excursiones.
 * Usa una base de datos temporal en memoria (no toca explora_rd).
 */
class DestinosExcursionesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_la_lista_de_destinos_muestra_todos(): void
    {
        $this->get('/destinos')
            ->assertOk()
            ->assertSee('Jarabacoa')
            ->assertSee('Santo Domingo')
            ->assertSee('Miches');
    }

    public function test_se_pueden_filtrar_destinos_por_tipo(): void
    {
        $this->get('/destinos?tipo=ciudad')
            ->assertOk()
            ->assertSee('Santo Domingo')
            ->assertDontSee('Bahía de las Águilas');
    }

    public function test_la_pagina_de_un_destino_muestra_actividades_y_excursiones(): void
    {
        $this->get('/destinos/jarabacoa')
            ->assertOk()
            ->assertSee('Qué hacer en Jarabacoa')
            ->assertSee('Rafting en el río Yaque del Norte')
            ->assertSee('Ruta de aventura por Jarabacoa');
    }

    public function test_un_destino_que_no_existe_da_error_404(): void
    {
        $this->get('/destinos/no-existe')->assertNotFound();
    }

    public function test_el_catalogo_busca_por_destino(): void
    {
        $this->get('/excursiones?destino=Punta+Cana')
            ->assertOk()
            ->assertSee('Catamarán a isla Saona')
            ->assertSee('Buggies y playa Macao')
            ->assertDontSee('Rafting en el río Yaque del Norte');
    }

    public function test_el_catalogo_filtra_por_categoria(): void
    {
        $this->get('/excursiones?categoria=gastronomia')
            ->assertOk()
            ->assertSee('Ruta gastronómica en la Zona Colonial')
            ->assertDontSee('Catamarán a isla Saona');
    }

    public function test_el_catalogo_filtra_circuitos_y_paquetes(): void
    {
        $this->get('/excursiones?tipo=circuitos')
            ->assertOk()
            ->assertSee('Ruta de aventura por Jarabacoa')
            ->assertSee('Experiencia de playa en Samaná')
            ->assertDontSee('Buggies y playa Macao');
    }

    public function test_el_catalogo_filtra_por_precio_maximo(): void
    {
        $this->get('/excursiones?precio=2500')
            ->assertOk()
            ->assertSee('Ruta gastronómica en la Zona Colonial')
            ->assertDontSee('Experiencia de playa en Samaná');
    }

    public function test_la_ficha_muestra_precio_duracion_ubicacion_y_agencia(): void
    {
        $this->get('/excursiones/catamaran-a-isla-saona')
            ->assertOk()
            ->assertSee('RD$ 5,900')
            ->assertSee('Día completo')
            ->assertSee('Bayahíbe, La Altagracia')
            ->assertSee('Brisa Tropical Tours')
            ->assertSee('https://wa.me/18095550202', false);
    }

    public function test_una_excursion_no_disponible_no_se_muestra(): void
    {
        Excursion::where('slug', 'buggies-y-playa-macao')->update(['disponible' => false]);

        $this->get('/excursiones/buggies-y-playa-macao')->assertNotFound();
        $this->get('/excursiones')->assertDontSee('Buggies y playa Macao');
    }
}
