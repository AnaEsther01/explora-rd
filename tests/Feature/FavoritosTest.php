<?php

namespace Tests\Feature;

use App\Models\Excursion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de los favoritos (el corazón ♥ de las excursiones).
 * (El FavoritoSeeder deja a María con «Bahía de las Águilas en bote» y «Ruta de aventura por Jarabacoa».)
 */
class FavoritosTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function usuario(string $correo): Usuario
    {
        return Usuario::where('correo', $correo)->firstOrFail();
    }

    private function saona(): Excursion
    {
        return Excursion::where('slug', 'catamaran-a-isla-saona')->firstOrFail();
    }

    public function test_el_turista_guarda_y_quita_un_favorito_sin_recargar(): void
    {
        $maria = $this->usuario('maria@correo.test');
        $this->actingAs($maria);

        // Guardar (como lo hace el JavaScript: pide JSON)
        $this->postJson('/excursiones/catamaran-a-isla-saona/favorito')
            ->assertOk()
            ->assertJson(['favorita' => true, 'total' => 3]);
        $this->assertDatabaseHas('favoritos', ['usuario_id' => $maria->id, 'excursion_id' => $this->saona()->id]);

        // Volver a tocar el corazón la quita
        $this->postJson('/excursiones/catamaran-a-isla-saona/favorito')
            ->assertOk()
            ->assertJson(['favorita' => false, 'total' => 2]);
        $this->assertDatabaseMissing('favoritos', ['usuario_id' => $maria->id, 'excursion_id' => $this->saona()->id]);
    }

    public function test_sin_javascript_tambien_funciona_y_vuelve_a_la_pagina(): void
    {
        $this->actingAs($this->usuario('maria@correo.test'))
            ->from('/excursiones')
            ->post('/excursiones/catamaran-a-isla-saona/favorito')
            ->assertRedirect('/excursiones')
            ->assertSessionHas('exito');
    }

    public function test_mi_cuenta_muestra_los_favoritos(): void
    {
        $this->actingAs($this->usuario('maria@correo.test'))
            ->get('/mi-cuenta')
            ->assertSee('Mis favoritos')
            ->assertSee('2 excursiones guardadas')
            ->assertSee('Bahía de las Águilas en bote')
            ->assertSee('Ruta de aventura por Jarabacoa');

        // John no tiene ninguno
        $this->actingAs($this->usuario('john@correo.test'))
            ->get('/mi-cuenta')
            ->assertSee('Todavía no has guardado excursiones.');
    }

    public function test_el_corazon_sale_lleno_en_las_guardadas(): void
    {
        $this->actingAs($this->usuario('maria@correo.test'))
            ->get('/excursiones/bahia-de-las-aguilas-en-bote')
            ->assertSee('En tus favoritos')
            ->assertSee('aria-pressed="true"', false);

        $this->get('/excursiones/catamaran-a-isla-saona')
            ->assertSee('Guardar en favoritos')
            ->assertDontSee('aria-pressed="true"', false);
    }

    public function test_quien_no_es_turista_no_ve_ni_usa_el_corazon(): void
    {
        // Sin sesión: el corazón lleva a «Entrar»
        $this->get('/excursiones')->assertSee('Inicia sesión para guardar en favoritos');
        $this->post('/excursiones/catamaran-a-isla-saona/favorito')->assertRedirect('/login');

        // Una agencia no tiene favoritos
        $agencia = $this->usuario('info@brisatropical.test');
        $this->actingAs($agencia)->get('/excursiones')->assertDontSee('btn-favorito', false);
        $this->post('/excursiones/catamaran-a-isla-saona/favorito')->assertRedirect(route('agencia.panel'));
        $this->assertDatabaseMissing('favoritos', ['usuario_id' => $agencia->id]);
    }

    public function test_una_excursion_oculta_no_se_guarda_ni_sale_en_mi_cuenta(): void
    {
        $maria = $this->usuario('maria@correo.test');
        $this->saona()->update(['disponible' => false]);
        Excursion::where('titulo', 'Bahía de las Águilas en bote')->update(['disponible' => false]);

        $this->actingAs($maria)->postJson('/excursiones/catamaran-a-isla-saona/favorito')->assertNotFound();

        $this->get('/mi-cuenta')->assertDontSee('Bahía de las Águilas en bote')->assertSee('1 excursión guardada');
    }

    public function test_la_agencia_ve_cuantas_veces_guardaron_sus_excursiones(): void
    {
        // Las 2 favoritas de María son de «Aventuras Jarabacoa»
        $this->actingAs($this->usuario('contacto@aventurasjarabacoa.test'))
            ->get('/agencia/panel')
            ->assertSee('guardadas en favoritos <strong>2 veces</strong>', false)
            ->assertSee('Calificación promedio');
    }

    public function test_los_favoritos_se_traducen(): void
    {
        $this->actingAs($this->usuario('maria@correo.test'));
        $this->get('/idioma/en');
        $this->get('/mi-cuenta')->assertSee('My favorites')->assertSee('2 saved tours');
    }
}
