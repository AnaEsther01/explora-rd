<?php

namespace Tests\Feature;

use App\Models\Excursion;
use App\Models\Resena;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de las reseñas y estrellas de las excursiones.
 * (El ResenaSeeder deja a María con «Buggies y playa Macao» ya hecha y sin reseña.)
 */
class ResenasTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function usuario(string $correo): Usuario
    {
        return Usuario::where('correo', $correo)->firstOrFail();
    }

    private function excursion(string $titulo): Excursion
    {
        return Excursion::where('titulo', $titulo)->firstOrFail();
    }

    public function test_la_ficha_muestra_el_promedio_y_las_resenas(): void
    {
        $this->get('/excursiones/catamaran-a-isla-saona')
            ->assertOk()
            ->assertSee('Opiniones de viajeros')
            ->assertSee('4.5')
            ->assertSee('2 reseñas')
            ->assertSee('La piscina natural es preciosa')
            ->assertSee('María G.')        // solo la inicial del apellido
            ->assertDontSee('María Gómez');
    }

    public function test_las_tarjetas_muestran_las_estrellas(): void
    {
        $this->get('/excursiones')->assertSee('tarjeta-valoracion', false)->assertSee('(1 reseña)');
    }

    public function test_el_catalogo_se_ordena_por_mejor_valoradas(): void
    {
        $respuesta = $this->get('/excursiones?orden=valoradas')->assertOk();
        $titulos = $respuesta->viewData('excursiones')->pluck('titulo')->all();

        $this->assertSame('Experiencia de playa en Samaná', $titulos[0]); // 5 estrellas
        $this->assertSame('Rafting en el río Yaque del Norte', $titulos[3]); // 3 estrellas, después de las de 4.5
    }

    public function test_quien_ya_hizo_la_excursion_puede_dejar_su_resena(): void
    {
        $maria = $this->usuario('maria@correo.test');
        $buggies = $this->excursion('Buggies y playa Macao');

        $this->actingAs($maria)->get('/mi-cuenta')->assertSee('Escribir reseña');
        $this->get("/excursiones/{$buggies->slug}")->assertSee('Publicar reseña');

        $this->post("/excursiones/{$buggies->slug}/resena", ['estrellas' => 4, 'comentario' => 'Agua fría pero hermoso.'])
            ->assertRedirect(route('excursiones.show', $buggies).'#resenas')
            ->assertSessionHas('exito');

        $this->assertDatabaseHas('resenas', ['usuario_id' => $maria->id, 'excursion_id' => $buggies->id, 'estrellas' => 4]);
        $this->get('/mi-cuenta')->assertSee('Ver mi reseña');
    }

    public function test_si_vuelve_a_enviar_se_actualiza_y_no_se_repite(): void
    {
        $maria = $this->usuario('maria@correo.test');
        $saona = $this->excursion('Catamarán a isla Saona'); // ya tiene reseña de 5

        $this->actingAs($maria)->get("/excursiones/{$saona->slug}")->assertSee('Edita tu reseña');

        $this->post("/excursiones/{$saona->slug}/resena", ['estrellas' => 3, 'comentario' => 'Cambié de opinión.'])
            ->assertSessionHas('exito');

        $this->assertSame(1, Resena::where('usuario_id', $maria->id)->where('excursion_id', $saona->id)->count());
        $this->assertSame(3, Resena::where('usuario_id', $maria->id)->where('excursion_id', $saona->id)->value('estrellas'));
    }

    public function test_no_puede_opinar_quien_no_hizo_la_excursion(): void
    {
        $maria = $this->usuario('maria@correo.test');
        $bahia = $this->excursion('Bahía de las Águilas en bote');       // nunca la reservó
        $rafting = $this->excursion('Rafting en el río Yaque del Norte'); // reserva pendiente y futura

        foreach ([$bahia, $rafting] as $excursion) {
            $this->actingAs($maria)->post("/excursiones/{$excursion->slug}/resena", ['estrellas' => 5])
                ->assertSessionHas('error');
            $this->assertDatabaseMissing('resenas', ['usuario_id' => $maria->id, 'excursion_id' => $excursion->id]);
        }

        $this->get("/excursiones/{$bahia->slug}")
            ->assertDontSee('Publicar reseña')
            ->assertSee('Podrás opinar cuando hayas hecho esta excursión');

        // Sin sesión: lo manda a iniciar sesión
        auth()->logout();
        $this->post("/excursiones/{$bahia->slug}/resena", ['estrellas' => 5])->assertRedirect('/login');
    }

    public function test_las_estrellas_deben_ser_de_1_a_5(): void
    {
        $buggies = $this->excursion('Buggies y playa Macao');
        $this->actingAs($this->usuario('maria@correo.test'));

        foreach ([0, 6, '', 'cinco'] as $mal) {
            $this->post("/excursiones/{$buggies->slug}/resena", ['estrellas' => $mal])->assertSessionHasErrors('estrellas');
        }
    }

    public function test_el_turista_borra_su_resena(): void
    {
        $maria = $this->usuario('maria@correo.test');
        $saona = $this->excursion('Catamarán a isla Saona');

        $this->actingAs($maria)->delete("/excursiones/{$saona->slug}/resena")->assertSessionHas('exito');

        $this->assertDatabaseMissing('resenas', ['usuario_id' => $maria->id, 'excursion_id' => $saona->id]);
        // La de John sigue ahí
        $this->assertSame(1, $saona->resenas()->count());
    }

    public function test_el_administrador_oculta_una_resena_y_deja_de_contar(): void
    {
        $admin = $this->usuario('admin@explorard.test');
        $rafting = $this->excursion('Rafting en el río Yaque del Norte');
        $resena = $rafting->resenas()->firstOrFail();

        $this->actingAs($admin)->get('/admin/resenas')->assertOk()->assertSee('Reseñas de los viajeros');

        $this->patch("/admin/resenas/{$resena->id}/visible")->assertSessionHas('exito');
        $this->assertFalse($resena->fresh()->visible);

        $this->assertNull($rafting->fresh()->promedioEstrellas());
        $this->get("/excursiones/{$rafting->slug}")->assertDontSee('we waited a long time');

        // Y la puede borrar
        $this->delete("/admin/resenas/{$resena->id}")->assertSessionHas('exito');
        $this->assertModelMissing($resena);
    }

    public function test_las_resenas_se_traducen(): void
    {
        $this->get('/idioma/en');
        $this->get('/excursiones/catamaran-a-isla-saona')->assertSee('Traveler reviews')->assertSee('2 reviews');

        $this->get('/idioma/fr');
        $this->get('/excursiones/catamaran-a-isla-saona')->assertSee('Avis des voyageurs');
    }
}
