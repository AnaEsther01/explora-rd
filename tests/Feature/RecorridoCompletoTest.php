<?php

namespace Tests\Feature;

use App\Models\Excursion;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Etapa 8: prueba general.
 * 1) Abre TODAS las páginas con cada tipo de usuario y revisa que carguen.
 * 2) Hace el recorrido completo del diagrama de flujo del Paso 5.
 */
class RecorridoCompletoTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_todas_las_paginas_publicas_cargan(): void
    {
        $paginas = [
            '/', '/destinos', '/destinos?tipo=playa', '/destinos/jarabacoa', '/destinos/santo-domingo',
            '/excursiones', '/excursiones?categoria=aventura', '/excursiones?tipo=circuitos&orden=precio_menor',
            '/excursiones/catamaran-a-isla-saona', '/agencias', '/agencias/2',
            '/login', '/registro', '/registro?tipo=agencia', '/olvide-mi-contrasena', '/sitemap.xml',
        ];

        foreach ($paginas as $pagina) {
            $this->get($pagina)->assertOk();
        }
    }

    public function test_las_paginas_privadas_cargan_para_su_rol(): void
    {
        $porRol = [
            'maria@correo.test' => ['/mi-cuenta'],
            'info@brisatropical.test' => [
                '/agencia/panel', '/agencia/perfil', '/agencia/excursiones', '/agencia/excursiones?ver=paquetes',
                '/agencia/excursiones/nueva', '/agencia/excursiones/catamaran-a-isla-saona/editar',
                '/agencia/reservas', '/agencia/reservas?estado=pendiente',
            ],
            'admin@explorard.test' => ['/admin', '/admin/reservas'],
        ];

        foreach ($porRol as $correo => $paginas) {
            $this->actingAs(Usuario::where('correo', $correo)->firstOrFail());
            foreach ($paginas as $pagina) {
                $this->get($pagina)->assertOk();
            }
        }
    }

    public function test_una_pagina_que_no_existe_muestra_el_error_en_espanol(): void
    {
        $this->get('/esta-pagina-no-existe')->assertNotFound()->assertSee('Página no encontrada');
    }

    public function test_las_paginas_tienen_cabeceras_de_seguridad(): void
    {
        $this->get('/')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_recorrido_completo_del_diagrama_de_flujo(): void
    {
        Notification::fake();

        // 1. El turista entra, busca un destino y encuentra resultados
        $this->get('/excursiones?destino=Samaná')->assertOk()->assertSee('Experiencia de playa en Samaná');

        // 2. Ve la ficha (fotos, precio, duración) y decide reservar, pero no ha iniciado sesión
        $this->get('/excursiones/experiencia-de-playa-en-samana')->assertOk()->assertSee('Inicia sesión para reservar');

        // 3. Se registra
        $this->post('/registro', [
            'rol' => 'turista', 'nombre' => 'Pedro Turista', 'correo' => 'pedro@correo.test',
            'contrasena' => 'viaje2026', 'contrasena_confirmation' => 'viaje2026',
        ])->assertRedirect(route('turista.panel'));

        // 4. Llena y envía la solicitud de reserva
        $this->post('/excursiones/experiencia-de-playa-en-samana/reservar', [
            'fecha' => now()->addMonth()->toDateString(), 'cantidad_personas' => 2,
        ])->assertRedirect(route('turista.panel'));
        $reserva = Reserva::latest('id')->firstOrFail();
        $this->assertSame('pendiente', $reserva->estado);
        $this->post('/salir');

        // 5. La agencia revisa la solicitud y, como hay disponibilidad, la confirma
        $agencia = Excursion::where('slug', 'experiencia-de-playa-en-samana')->firstOrFail()->agencia;
        $this->actingAs($agencia)->get('/agencia/reservas?estado=pendiente')->assertSee('Pedro Turista');
        $this->patch("/agencia/reservas/{$reserva->id}/confirmar");

        // 6. El turista ve su reserva confirmada (y recibió el aviso)
        $pedro = Usuario::where('correo', 'pedro@correo.test')->firstOrFail();
        $this->actingAs($pedro)->get('/mi-cuenta')->assertSee('Confirmada');
        $this->assertSame('confirmada', $reserva->fresh()->estado);
    }
}
