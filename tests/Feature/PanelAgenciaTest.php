<?php

namespace Tests\Feature;

use App\Models\Destino;
use App\Models\Excursion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Pruebas de la Etapa 6: panel de agencias y perfil público.
 * Usa una base de datos temporal y un disco de fotos falso (no toca tus archivos).
 */
class PanelAgenciaTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function aventuras(): Usuario
    {
        return Usuario::where('correo', 'contacto@aventurasjarabacoa.test')->firstOrFail();
    }

    private function brisa(): Usuario
    {
        return Usuario::where('correo', 'info@brisatropical.test')->firstOrFail();
    }

    private function datosExcursion(array $cambios = []): array
    {
        return array_merge([
            'titulo' => 'Cabalgata al Salto de Baiguate',
            'tipo' => 'excursion',
            'categoria' => 'familia',
            'destino_id' => Destino::where('nombre', 'Jarabacoa')->value('id'),
            'descripcion' => 'Paseo a caballo por senderos de montaña hasta el salto.',
            'precio' => 1800,
            'duracion' => '3 horas',
            'ubicacion' => 'Jarabacoa, La Vega',
            'disponible' => '1',
        ], $cambios);
    }

    // ---------- Secciones del panel ----------

    public function test_la_agencia_ve_todas_las_secciones_de_su_panel(): void
    {
        $this->actingAs($this->aventuras());

        $this->get('/agencia/panel')->assertOk()->assertSee('Visitas al perfil');
        $this->get('/agencia/perfil')->assertOk()->assertSee('Logotipo y presentación');
        $this->get('/agencia/excursiones')->assertOk()->assertSee('Rafting en el río Yaque del Norte');
        $this->get('/agencia/excursiones?ver=paquetes')->assertOk()->assertSee('Ruta de aventura por Jarabacoa');
        $this->get('/agencia/excursiones/nueva')->assertOk()->assertSee('Publicar una experiencia nueva');
    }

    public function test_cada_agencia_ve_solo_sus_excursiones(): void
    {
        $this->actingAs($this->aventuras())
            ->get('/agencia/excursiones')
            ->assertSee('Rafting en el río Yaque del Norte')
            ->assertDontSee('Buggies y playa Macao'); // es de Brisa Tropical
    }

    // ---------- Crear, editar, ocultar y eliminar ----------

    public function test_la_agencia_publica_una_excursion_con_foto(): void
    {
        Storage::fake('public');
        $this->actingAs($this->aventuras());

        $this->post('/agencia/excursiones', $this->datosExcursion([
            'foto' => UploadedFile::fake()->image('cabalgata.png', 1200, 800),
        ]))->assertRedirect(route('agencia.excursiones.index'));

        $excursion = Excursion::where('titulo', 'Cabalgata al Salto de Baiguate')->firstOrFail();
        $this->assertSame($this->aventuras()->id, $excursion->agencia_id);
        $this->assertSame('cabalgata-al-salto-de-baiguate', $excursion->slug);
        $this->assertStringStartsWith('storage/excursiones/', $excursion->foto);
        Storage::disk('public')->assertExists(substr($excursion->foto, strlen('storage/')));

        // Ya aparece en el catálogo público
        $this->get('/excursiones')->assertSee('Cabalgata al Salto de Baiguate');
    }

    public function test_dos_excursiones_con_el_mismo_titulo_tienen_direcciones_distintas(): void
    {
        $this->actingAs($this->aventuras())->post('/agencia/excursiones', $this->datosExcursion());
        $this->actingAs($this->brisa())->post('/agencia/excursiones', $this->datosExcursion());

        $slugs = Excursion::where('titulo', 'Cabalgata al Salto de Baiguate')->pluck('slug')->sort()->values()->all();
        $this->assertSame(['cabalgata-al-salto-de-baiguate', 'cabalgata-al-salto-de-baiguate-2'], $slugs);
    }

    public function test_los_datos_obligatorios_se_revisan(): void
    {
        $this->actingAs($this->aventuras())
            ->post('/agencia/excursiones', ['titulo' => ''])
            ->assertSessionHasErrors(['titulo', 'tipo', 'destino_id', 'descripcion', 'precio']);
    }

    public function test_la_agencia_edita_su_excursion(): void
    {
        $excursion = Excursion::where('slug', 'rafting-en-el-rio-yaque-del-norte')->firstOrFail();

        $this->actingAs($this->aventuras())
            ->put("/agencia/excursiones/{$excursion->slug}", $this->datosExcursion([
                'titulo' => $excursion->titulo,
                'precio' => 3900,
            ]))->assertRedirect();

        $this->assertEquals(3900, $excursion->fresh()->precio);
    }

    public function test_una_agencia_no_puede_editar_ni_borrar_excursiones_de_otra(): void
    {
        $ajena = Excursion::where('slug', 'buggies-y-playa-macao')->firstOrFail(); // de Brisa Tropical
        $this->actingAs($this->aventuras());

        $this->get("/agencia/excursiones/{$ajena->slug}/editar")->assertForbidden();
        $this->put("/agencia/excursiones/{$ajena->slug}", $this->datosExcursion())->assertForbidden();
        $this->delete("/agencia/excursiones/{$ajena->slug}")->assertForbidden();
        $this->assertDatabaseHas('excursiones', ['id' => $ajena->id]);
    }

    public function test_ocultar_una_excursion_la_quita_del_catalogo(): void
    {
        $excursion = Excursion::where('slug', 'saltos-de-jimenoa-y-baiguate')->firstOrFail();

        $this->actingAs($this->aventuras())->patch("/agencia/excursiones/{$excursion->slug}/disponible");

        $this->assertFalse($excursion->fresh()->disponible);
        // (se revisa el enlace porque el mensaje verde «… quedó oculta» también lleva el título)
        $this->get('/excursiones')->assertDontSee('/excursiones/saltos-de-jimenoa-y-baiguate');
    }

    public function test_no_se_borra_una_excursion_con_reservas_pero_si_una_sin_reservas(): void
    {
        $this->actingAs($this->aventuras());

        // Rafting tiene una reserva de prueba: no se borra
        $conReservas = Excursion::where('slug', 'rafting-en-el-rio-yaque-del-norte')->firstOrFail();
        $this->delete("/agencia/excursiones/{$conReservas->slug}")->assertSessionHas('error');
        $this->assertDatabaseHas('excursiones', ['id' => $conReservas->id]);

        // Saltos no tiene reservas: sí se borra
        $sinReservas = Excursion::where('slug', 'saltos-de-jimenoa-y-baiguate')->firstOrFail();
        $this->delete("/agencia/excursiones/{$sinReservas->slug}")->assertSessionHas('exito');
        $this->assertDatabaseMissing('excursiones', ['id' => $sinReservas->id]);
    }

    // ---------- Mi perfil ----------

    public function test_la_agencia_actualiza_su_perfil_con_logo_fotos_y_videos(): void
    {
        Storage::fake('public');
        $agencia = $this->aventuras();

        $this->actingAs($agencia)->put('/agencia/perfil', [
            'nombre_comercial' => 'Aventuras Jarabacoa RD',
            'nombre' => 'Luis Peña',
            'correo' => $agencia->correo,
            'whatsapp' => '+1 809 555 0101',
            'descripcion' => 'Nueva presentación de la agencia.',
            'logo' => UploadedFile::fake()->image('logo.png', 400, 400),
            'fotos_nuevas' => [UploadedFile::fake()->image('foto1.png'), UploadedFile::fake()->image('foto2.png')],
            'videos' => "https://www.youtube.com/watch?v=dQw4w9WgXcQ\nhttps://vimeo.com/76979871",
        ])->assertRedirect(route('agencia.perfil'))->assertSessionHas('exito');

        $agencia->refresh();
        $this->assertSame('Aventuras Jarabacoa RD', $agencia->nombre_comercial);
        $this->assertSame('18095550101', $agencia->whatsapp);
        $this->assertStringStartsWith('storage/logos/', $agencia->logo);
        $this->assertCount(4, $agencia->fotos); // 2 de ejemplo + 2 nuevas
        $this->assertSame([
            'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            'https://player.vimeo.com/video/76979871',
        ], $agencia->videosParaMostrar());
    }

    public function test_solo_se_aceptan_videos_de_youtube_o_vimeo(): void
    {
        $agencia = $this->aventuras();

        $this->actingAs($agencia)->put('/agencia/perfil', [
            'nombre_comercial' => 'Aventuras Jarabacoa',
            'nombre' => 'Luis Peña',
            'correo' => $agencia->correo,
            'whatsapp' => '18095550101',
            'videos' => 'https://sitio-raro.test/video',
        ])->assertSessionHasErrors('videos');
    }

    public function test_un_archivo_que_no_es_imagen_se_rechaza(): void
    {
        Storage::fake('public');
        $this->actingAs($this->aventuras())
            ->post('/agencia/excursiones', $this->datosExcursion([
                'foto' => UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream'),
            ]))->assertSessionHasErrors('foto');
    }

    // ---------- Perfil público ----------

    public function test_el_perfil_publico_muestra_la_agencia_y_cuenta_visitas(): void
    {
        $agencia = $this->brisa();

        $this->get("/agencias/{$agencia->id}")
            ->assertOk()
            ->assertSee('Brisa Tropical Tours')
            ->assertSee('Catamarán a isla Saona')
            ->assertSee('https://wa.me/18095550202', false);

        $this->assertSame(1, $agencia->fresh()->visitas);
        $this->get('/agencias')->assertOk()->assertSee('Aventuras Jarabacoa');
    }

    public function test_un_turista_no_tiene_perfil_publico_de_agencia(): void
    {
        $turista = Usuario::where('correo', 'maria@correo.test')->firstOrFail();

        $this->get("/agencias/{$turista->id}")->assertNotFound();
    }

    public function test_un_turista_no_entra_al_panel_de_excursiones(): void
    {
        $turista = Usuario::where('correo', 'maria@correo.test')->firstOrFail();

        $this->actingAs($turista)->get('/agencia/excursiones')->assertRedirect(route('turista.panel'));
    }
}
