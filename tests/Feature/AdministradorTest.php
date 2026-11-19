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
 * Pruebas del panel del administrador: usuarios, agencias, destinos y excursiones.
 */
class AdministradorTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function usuario(string $correo): Usuario
    {
        return Usuario::where('correo', $correo)->firstOrFail();
    }

    private function admin(): Usuario
    {
        return $this->usuario('admin@explorard.test');
    }

    public function test_el_administrador_abre_todas_las_secciones(): void
    {
        $this->actingAs($this->admin());

        foreach (['/admin', '/admin/usuarios', '/admin/usuarios?rol=agencia', '/admin/usuarios/nuevo',
                  '/admin/usuarios/2/editar', '/admin/destinos', '/admin/destinos/nuevo',
                  '/admin/destinos/jarabacoa/editar', '/admin/excursiones', '/admin/reservas'] as $pagina) {
            $this->get($pagina)->assertOk();
        }
    }

    public function test_otros_roles_no_entran_al_panel_del_administrador(): void
    {
        $this->actingAs($this->usuario('info@brisatropical.test'))->get('/admin/usuarios')->assertRedirect(route('agencia.panel'));
        $this->actingAs($this->usuario('maria@correo.test'))->delete('/admin/destinos/miches')->assertRedirect(route('turista.panel'));
        $this->assertDatabaseHas('destinos', ['slug' => 'miches']);
    }

    // ---------- Usuarios ----------

    public function test_crea_una_agencia_y_un_administrador(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/usuarios', [
            'rol' => 'agencia', 'nombre' => 'Rosa Díaz', 'correo' => 'rosa@samanatours.test',
            'nombre_comercial' => 'Samaná Tours', 'whatsapp' => '809 555 7777',
            'contrasena' => 'clave1234', 'contrasena_confirmation' => 'clave1234',
        ])->assertRedirect(route('admin.usuarios.index'));

        $this->post('/admin/usuarios', [
            'rol' => 'administrador', 'nombre' => 'Otro Admin', 'correo' => 'otro@explorard.test',
            'contrasena' => 'clave1234', 'contrasena_confirmation' => 'clave1234',
        ]);

        $this->assertSame('8095557777', $this->usuario('rosa@samanatours.test')->whatsapp);
        $this->assertSame('administrador', $this->usuario('otro@explorard.test')->rol);
    }

    public function test_edita_un_usuario_sin_cambiar_su_contrasena(): void
    {
        $maria = $this->usuario('maria@correo.test');
        $claveAntes = $maria->contrasena;

        $this->actingAs($this->admin())->put("/admin/usuarios/{$maria->id}", [
            'rol' => 'turista', 'nombre' => 'María Gómez Pérez', 'correo' => 'maria@correo.test', 'telefono' => '829-555-0303',
        ])->assertRedirect(route('admin.usuarios.index'));

        $maria->refresh();
        $this->assertSame('María Gómez Pérez', $maria->nombre);
        $this->assertSame($claveAntes, $maria->contrasena);
    }

    public function test_suspender_una_agencia_esconde_su_contenido_y_no_la_deja_entrar(): void
    {
        $brisa = $this->usuario('info@brisatropical.test');

        $this->actingAs($this->admin())->patch("/admin/usuarios/{$brisa->id}/activo")->assertSessionHas('exito');
        $this->assertFalse($brisa->fresh()->activo);
        $this->post('/salir');

        // Su perfil y sus excursiones ya no se ven
        $this->get("/agencias/{$brisa->id}")->assertNotFound();
        $this->get('/excursiones/catamaran-a-isla-saona')->assertNotFound();
        $this->get('/excursiones')->assertDontSee('/excursiones/catamaran-a-isla-saona');

        // No puede iniciar sesión
        $this->post('/login', ['correo' => 'info@brisatropical.test', 'contrasena' => 'password'])
            ->assertSessionHasErrors('correo');
        $this->assertGuest();

        // Al reactivarla, todo vuelve
        $this->actingAs($this->admin())->patch("/admin/usuarios/{$brisa->id}/activo");
        $this->get('/excursiones/catamaran-a-isla-saona')->assertOk();
    }

    public function test_si_la_suspenden_con_la_sesion_abierta_la_sacan_del_panel(): void
    {
        $brisa = $this->usuario('info@brisatropical.test');
        $brisa->update(['activo' => false]);

        $this->actingAs($brisa)->get('/agencia/panel')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_el_administrador_no_puede_suspenderse_borrarse_ni_quitarse_el_rol(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->patch("/admin/usuarios/{$admin->id}/activo")->assertSessionHas('error');
        $this->delete("/admin/usuarios/{$admin->id}")->assertSessionHas('error');
        $this->put("/admin/usuarios/{$admin->id}", [
            'rol' => 'turista', 'nombre' => $admin->nombre, 'correo' => $admin->correo,
        ])->assertSessionHas('error');

        $admin->refresh();
        $this->assertTrue($admin->activo);
        $this->assertSame('administrador', $admin->rol);
    }

    public function test_no_se_borra_un_usuario_con_reservas_pero_si_uno_sin_reservas(): void
    {
        $this->actingAs($this->admin());

        $maria = $this->usuario('maria@correo.test'); // tiene reservas
        $this->delete("/admin/usuarios/{$maria->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('usuarios', ['id' => $maria->id]);

        $nuevo = Usuario::create(['nombre' => 'Sin Reservas', 'correo' => 'sin@reservas.test', 'contrasena' => 'clave1234', 'rol' => 'turista']);
        $this->delete("/admin/usuarios/{$nuevo->id}")->assertSessionHas('exito');
        $this->assertDatabaseMissing('usuarios', ['id' => $nuevo->id]);
    }

    // ---------- Destinos ----------

    public function test_crea_edita_y_borra_un_destino(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $this->post('/admin/destinos', [
            'nombre' => 'Las Terrenas', 'provincia' => 'Samaná', 'tipo' => 'playa',
            'descripcion' => 'Playas y ambiente internacional. Un pueblo de pescadores con playas de arena dorada.',
            'actividades' => "Playa Bonita\nPlaya Cosón\n\nSalto El Limón",
            'foto' => UploadedFile::fake()->image('terrenas.png', 1200, 800),
        ])->assertRedirect(route('admin.destinos.index'));

        $destino = Destino::where('nombre', 'Las Terrenas')->firstOrFail();
        $this->assertSame('las-terrenas', $destino->slug);
        $this->assertSame(['Playa Bonita', 'Playa Cosón', 'Salto El Limón'], $destino->actividades);
        $this->assertStringStartsWith('storage/destinos/', $destino->foto);
        $this->get('/destinos/las-terrenas')->assertOk()->assertSee('Playa Cosón');

        $this->put('/admin/destinos/las-terrenas', [
            'nombre' => 'Las Terrenas', 'provincia' => 'Samaná', 'tipo' => 'playa',
            'descripcion' => 'Descripción nueva.', 'actividades' => 'Kitesurf',
        ]);
        $this->assertSame(['Kitesurf'], $destino->fresh()->actividades);

        $this->delete('/admin/destinos/las-terrenas')->assertSessionHas('exito');
        $this->assertDatabaseMissing('destinos', ['nombre' => 'Las Terrenas']);
    }

    public function test_no_se_borra_un_destino_con_excursiones_ni_se_repite_el_nombre(): void
    {
        $this->actingAs($this->admin());

        $this->delete('/admin/destinos/jarabacoa')->assertSessionHas('error');
        $this->assertDatabaseHas('destinos', ['slug' => 'jarabacoa']);

        $this->post('/admin/destinos', [
            'nombre' => 'Jarabacoa', 'provincia' => 'La Vega', 'tipo' => 'montaña', 'descripcion' => 'Repetido.',
        ])->assertSessionHasErrors('nombre');
    }

    // ---------- Excursiones ----------

    public function test_modera_excursiones_de_cualquier_agencia(): void
    {
        $this->actingAs($this->admin());
        $saltos = Excursion::where('slug', 'saltos-de-jimenoa-y-baiguate')->firstOrFail(); // sin reservas
        $rafting = Excursion::where('slug', 'rafting-en-el-rio-yaque-del-norte')->firstOrFail(); // con reservas

        $this->get('/admin/excursiones?agencia='.$saltos->agencia_id)->assertSee('Saltos de Jimenoa y Baiguate')->assertDontSee('Buggies y playa Macao');

        $this->patch("/admin/excursiones/{$saltos->slug}/disponible");
        $this->assertFalse($saltos->fresh()->disponible);

        $this->delete("/admin/excursiones/{$rafting->slug}")->assertSessionHas('error');
        $this->delete("/admin/excursiones/{$saltos->slug}")->assertSessionHas('exito');
        $this->assertDatabaseMissing('excursiones', ['id' => $saltos->id]);
    }
}
