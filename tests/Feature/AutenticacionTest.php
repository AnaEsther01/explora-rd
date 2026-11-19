<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Pruebas de la Etapa 5: registro, inicio de sesión, roles y contraseña.
 * Usa una base de datos temporal en memoria (no toca explora_rd).
 */
class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    // ---------- Páginas ----------

    public function test_las_paginas_de_entrar_y_registro_cargan(): void
    {
        $this->get('/login')->assertOk()->assertSee('Iniciar sesión')->assertSee('Bienvenido de nuevo');
        $this->get('/registro')->assertOk()->assertSee('Soy turista');
        $this->get('/registro?tipo=agencia')->assertOk()->assertSee('Registrar mi agencia');
    }

    // ---------- Registro ----------

    public function test_un_turista_puede_registrarse(): void
    {
        $respuesta = $this->post('/registro', [
            'rol' => 'turista',
            'nombre' => 'Laura Pérez',
            'correo' => 'laura@correo.test',
            'contrasena' => 'clave1234',
            'contrasena_confirmation' => 'clave1234',
        ]);

        $respuesta->assertRedirect(route('turista.panel'));
        $this->assertAuthenticated();

        $usuario = Usuario::where('correo', 'laura@correo.test')->first();
        $this->assertSame('turista', $usuario->rol);
        $this->assertNotSame('clave1234', $usuario->contrasena); // se guardó cifrada
    }

    public function test_una_agencia_puede_registrarse_y_su_whatsapp_queda_solo_con_numeros(): void
    {
        $this->post('/registro', [
            'rol' => 'agencia',
            'nombre' => 'Pedro Díaz',
            'nombre_comercial' => 'Caribe Azul Tours',
            'correo' => 'info@caribeazul.test',
            'whatsapp' => '+1 (809) 555-1234',
            'contrasena' => 'clave1234',
            'contrasena_confirmation' => 'clave1234',
        ])->assertRedirect(route('agencia.panel'));

        $agencia = Usuario::where('correo', 'info@caribeazul.test')->first();
        $this->assertSame('agencia', $agencia->rol);
        $this->assertSame('18095551234', $agencia->whatsapp);
    }

    public function test_una_agencia_debe_escribir_nombre_comercial_y_whatsapp(): void
    {
        $this->post('/registro', [
            'rol' => 'agencia',
            'nombre' => 'Sin Datos',
            'correo' => 'sin@datos.test',
            'contrasena' => 'clave1234',
            'contrasena_confirmation' => 'clave1234',
        ])->assertSessionHasErrors(['nombre_comercial', 'whatsapp']);

        $this->assertGuest();
    }

    public function test_nadie_puede_registrarse_como_administrador(): void
    {
        $this->post('/registro', [
            'rol' => 'administrador',
            'nombre' => 'Intruso',
            'correo' => 'intruso@correo.test',
            'contrasena' => 'clave1234',
            'contrasena_confirmation' => 'clave1234',
        ])->assertSessionHasErrors('rol');

        $this->assertDatabaseMissing('usuarios', ['correo' => 'intruso@correo.test']);
    }

    public function test_no_se_puede_repetir_un_correo(): void
    {
        $this->post('/registro', [
            'rol' => 'turista',
            'nombre' => 'Otra María',
            'correo' => 'maria@correo.test', // ya existe en los datos de prueba
            'contrasena' => 'clave1234',
            'contrasena_confirmation' => 'clave1234',
        ])->assertSessionHasErrors('correo');
    }

    // ---------- Inicio y cierre de sesión ----------

    public function test_cada_rol_entra_a_su_propio_panel(): void
    {
        $casos = [
            'maria@correo.test' => 'turista.panel',
            'contacto@aventurasjarabacoa.test' => 'agencia.panel',
            'admin@explorard.test' => 'admin.panel',
        ];

        foreach ($casos as $correo => $panel) {
            $this->post('/login', ['correo' => $correo, 'contrasena' => 'password'])
                ->assertRedirect(route($panel));
            $this->get(route($panel))->assertOk();
            $this->post('/salir');
        }
    }

    public function test_con_contrasena_incorrecta_no_entra(): void
    {
        $this->post('/login', ['correo' => 'maria@correo.test', 'contrasena' => 'incorrecta'])
            ->assertSessionHasErrors('correo');

        $this->assertGuest();
    }

    public function test_cerrar_sesion(): void
    {
        $this->actingAs(Usuario::where('correo', 'maria@correo.test')->first());

        $this->post('/salir')->assertRedirect(route('inicio'));
        $this->assertGuest();
    }

    // ---------- Middleware de roles ----------

    public function test_sin_sesion_los_paneles_mandan_a_entrar(): void
    {
        $this->get('/mi-cuenta')->assertRedirect(route('login'));
        $this->get('/agencia/panel')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_una_agencia_no_puede_entrar_al_panel_del_administrador(): void
    {
        $agencia = Usuario::where('correo', 'contacto@aventurasjarabacoa.test')->first();

        $this->actingAs($agencia)->get('/admin')
            ->assertRedirect(route('agencia.panel'))
            ->assertSessionHas('error');
    }

    public function test_un_turista_no_puede_entrar_al_panel_de_agencia(): void
    {
        $turista = Usuario::where('correo', 'maria@correo.test')->first();

        $this->actingAs($turista)->get('/agencia/panel')->assertRedirect(route('turista.panel'));
    }

    public function test_con_sesion_iniciada_la_pagina_de_entrar_lleva_al_panel(): void
    {
        $admin = Usuario::where('correo', 'admin@explorard.test')->first();

        $this->actingAs($admin)->get('/login')->assertRedirect(route('admin.panel'));
    }

    // ---------- Recuperar contraseña ----------

    public function test_se_puede_recuperar_la_contrasena(): void
    {
        Notification::fake();
        $usuario = Usuario::where('correo', 'maria@correo.test')->first();

        $this->post('/olvide-mi-contrasena', ['correo' => 'maria@correo.test'])->assertSessionHas('exito');

        Notification::assertSentTo($usuario, ResetPassword::class, function ($aviso) {
            $this->post('/restablecer-contrasena', [
                'token' => $aviso->token,
                'correo' => 'maria@correo.test',
                'contrasena' => 'nueva1234',
                'contrasena_confirmation' => 'nueva1234',
            ])->assertRedirect(route('login'));

            return true;
        });

        $this->post('/login', ['correo' => 'maria@correo.test', 'contrasena' => 'nueva1234'])
            ->assertRedirect(route('turista.panel'));
    }
}
