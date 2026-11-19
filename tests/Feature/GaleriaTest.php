<?php

namespace Tests\Feature;

use App\Models\Excursion;
use App\Support\Creditos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de la página de galería (/galeria).
 * Usa las fotos que ya están en public/img (destinos y excursiones).
 */
class GaleriaTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_la_galeria_muestra_las_fotos_de_destinos_y_excursiones(): void
    {
        $this->get('/galeria')
            ->assertOk()
            ->assertSee('República Dominicana en imágenes')
            ->assertSee('img/destinos/jarabacoa.jpg', false)
            ->assertSee('img/excursiones/catamaran-saona.jpg', false)
            ->assertSee('data-filtros="montaña destino"', false)
            ->assertSee('data-filtros="playa excursion"', false)
            ->assertSee('id="visorGaleria"', false)
            ->assertSee('js/galeria.js', false);
    }

    public function test_cada_foto_lleva_el_credito_de_su_autor(): void
    {
        $this->assertSame('Eriel Ezequiel Reyes Saviñon', Creditos::de('img/destinos/jarabacoa.jpg')['autor']);
        $this->assertSame('Pexels', Creditos::de('img/excursiones/saltos-jarabacoa.jpg')['fuente']);
        $this->assertNull(Creditos::de('img/no-existe.jpg'));

        $this->get('/galeria')->assertSee('data-autor="Eriel Ezequiel Reyes Saviñon"', false);
    }

    public function test_las_excursiones_ocultas_no_salen_en_la_galeria(): void
    {
        Excursion::where('slug', 'saltos-de-jimenoa-y-baiguate')->update(['disponible' => false]);

        $this->get('/galeria')->assertDontSee('img/excursiones/saltos-jarabacoa.jpg', false);
    }

    public function test_una_foto_repetida_sale_una_sola_vez(): void
    {
        // En los datos de prueba, Brisa Tropical usa en su perfil la misma foto del catamarán
        $html = $this->get('/galeria')->getContent();

        $this->assertSame(1, substr_count($html, 'data-src="'.asset('img/excursiones/catamaran-saona.jpg').'"'));
    }

    public function test_la_portada_y_el_pie_enlazan_la_galeria(): void
    {
        $this->get('/')->assertSee('Ver toda la galería')->assertSee(route('galeria'));
        $this->get('/sitemap.xml')->assertSee(route('galeria'));
    }

    public function test_la_galeria_se_traduce(): void
    {
        $this->get('/idioma/en');
        $this->get('/galeria')->assertSee('Tap a photo to see it full size')->assertSee('See destination');
    }
}
