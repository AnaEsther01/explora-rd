<?php

namespace Tests\Feature;

use App\Models\Destino;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas del mapa de destinos (Leaflet + OpenStreetMap).
 */
class MapaTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /** Lee los destinos que la página le pasa al mapa (atributo data-destinos). */
    private function destinosDelMapa(string $html): array
    {
        preg_match('/data-destinos="([^"]+)"/', $html, $m);
        $this->assertNotEmpty($m, 'La página no tiene mapa');

        return json_decode(html_entity_decode($m[1]), true);
    }

    public function test_la_pagina_de_destinos_muestra_el_mapa_con_todos(): void
    {
        $respuesta = $this->get('/destinos')
            ->assertOk()
            ->assertSee('Explora en el mapa')
            ->assertSee('leaflet@1.9.4/dist/leaflet.js', false)
            ->assertSee('js/mapa.js', false);

        $puntos = $this->destinosDelMapa($respuesta->getContent());
        $this->assertCount(Destino::count(), $puntos);

        $jarabacoa = collect($puntos)->firstWhere('nombre', 'Jarabacoa');
        $this->assertEqualsWithDelta(19.1167, $jarabacoa['lat'], 0.0001);
        $this->assertEqualsWithDelta(-70.6333, $jarabacoa['lng'], 0.0001);
        $this->assertStringEndsWith('/destinos/jarabacoa', $jarabacoa['url']);
    }

    public function test_el_filtro_por_tipo_tambien_filtra_el_mapa(): void
    {
        $puntos = $this->destinosDelMapa($this->get('/destinos?tipo=ciudad')->getContent());

        $this->assertEqualsCanonicalizing(['Santo Domingo', 'Santiago'], array_column($puntos, 'nombre'));
    }

    public function test_cada_destino_tiene_su_mapa_y_como_llegar(): void
    {
        $respuesta = $this->get('/destinos/samana')
            ->assertOk()
            ->assertSee('mapa-pequeno', false)
            ->assertSee('https://www.google.com/maps/dir/?api=1&amp;destination=19.2056,-69.3365', false);

        $this->assertSame(['Samaná'], array_column($this->destinosDelMapa($respuesta->getContent()), 'nombre'));
    }

    public function test_un_destino_sin_coordenadas_no_sale_en_el_mapa(): void
    {
        Destino::where('slug', 'miches')->update(['latitud' => null, 'longitud' => null]);

        $puntos = $this->destinosDelMapa($this->get('/destinos')->getContent());
        $this->assertNotContains('Miches', array_column($puntos, 'nombre'));

        // Y su página se ve bien, solo que sin mapa
        $this->get('/destinos/miches')->assertOk()->assertDontSee('mapa-pequeno', false);
    }

    public function test_el_administrador_guarda_las_coordenadas_y_se_revisan(): void
    {
        $admin = Usuario::where('correo', 'admin@explorard.test')->firstOrFail();
        $destino = Destino::where('slug', 'barahona')->firstOrFail();
        $datos = [
            'nombre' => $destino->nombre,
            'provincia' => $destino->provincia,
            'tipo' => $destino->tipo,
            'descripcion' => $destino->descripcion,
        ];

        // Sin el signo menos, la longitud queda fuera de República Dominicana
        $this->actingAs($admin)->put("/admin/destinos/{$destino->slug}", $datos + ['latitud' => 18.2, 'longitud' => 71.1])
            ->assertSessionHasErrors('longitud');

        // Solo una de las dos no vale
        $this->put("/admin/destinos/{$destino->slug}", $datos + ['latitud' => 18.2])
            ->assertSessionHasErrors('longitud');

        $this->put("/admin/destinos/{$destino->slug}", $datos + ['latitud' => 18.25, 'longitud' => -71.15])
            ->assertSessionHasNoErrors();
        $this->assertEqualsWithDelta(-71.15, $destino->fresh()->longitud, 0.0001);
    }

    public function test_los_datos_de_produccion_traen_la_ubicacion_de_los_destinos(): void
    {
        // Simula un servidor nuevo: destinos sin coordenadas
        Destino::query()->update(['latitud' => null, 'longitud' => null]);

        $this->seed(\Database\Seeders\ProduccionSeeder::class);

        $this->assertSame(0, Destino::whereNull('latitud')->count());
    }

    public function test_el_mapa_se_traduce(): void
    {
        $this->get('/idioma/fr');
        $respuesta = $this->get('/destinos')->assertSee('Explorez sur la carte');

        $puntos = $this->destinosDelMapa($respuesta->getContent());
        $this->assertSame('Montagne', collect($puntos)->firstWhere('nombre', 'Jarabacoa')['tipoNombre']);
    }
}
