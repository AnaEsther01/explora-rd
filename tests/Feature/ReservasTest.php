<?php

namespace Tests\Feature;

use App\Models\Excursion;
use App\Models\Reserva;
use App\Models\Usuario;
use App\Notifications\EstadoReservaNotification;
use App\Notifications\NuevaReservaNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Pruebas de la Etapa 7: sistema de reservas.
 * Usa una base de datos temporal y correos falsos (no se envía nada).
 */
class ReservasTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function usuario(string $correo): Usuario
    {
        return Usuario::where('correo', $correo)->firstOrFail();
    }

    private function saona(): Excursion
    {
        return Excursion::where('slug', 'catamaran-a-isla-saona')->firstOrFail(); // de Brisa Tropical
    }

    // ---------- El turista solicita ----------

    public function test_un_turista_solicita_una_reserva_y_la_agencia_recibe_un_correo(): void
    {
        Notification::fake();
        $maria = $this->usuario('maria@correo.test');
        $fecha = now()->addDays(15)->toDateString();

        $this->actingAs($maria)->post('/excursiones/catamaran-a-isla-saona/reservar', [
            'fecha' => $fecha,
            'cantidad_personas' => 3,
            'comentario' => 'Celebramos un cumpleaños.',
        ])->assertRedirect(route('turista.panel'))->assertSessionHas('exito');

        // (María ya hizo esta excursión antes; buscamos la reserva nueva por su fecha)
        $reserva = Reserva::where('usuario_id', $maria->id)->where('excursion_id', $this->saona()->id)
            ->whereDate('fecha', $fecha)->firstOrFail();
        $this->assertSame('pendiente', $reserva->estado);
        $this->assertSame(3, $reserva->cantidad_personas);
        $this->assertEquals(17700, $reserva->total()); // 3 × 5,900

        Notification::assertSentTo($this->usuario('info@brisatropical.test'), NuevaReservaNotification::class);
    }

    public function test_sin_sesion_no_se_puede_reservar_y_la_ficha_invita_a_entrar(): void
    {
        $this->post('/excursiones/catamaran-a-isla-saona/reservar', [
            'fecha' => now()->addDays(5)->toDateString(),
            'cantidad_personas' => 2,
        ])->assertRedirect(route('login'));

        $this->get('/excursiones/catamaran-a-isla-saona')->assertSee('Inicia sesión para reservar');
    }

    public function test_despues_de_entrar_vuelve_a_la_ficha_de_la_excursion(): void
    {
        $this->get('/login?volver=/excursiones/catamaran-a-isla-saona');

        $this->post('/login', ['correo' => 'maria@correo.test', 'contrasena' => 'password'])
            ->assertRedirect('/excursiones/catamaran-a-isla-saona');
    }

    public function test_no_se_acepta_volver_a_otra_pagina_web(): void
    {
        $this->get('/login?volver=//sitio-malo.test');

        $this->post('/login', ['correo' => 'maria@correo.test', 'contrasena' => 'password'])
            ->assertRedirect(route('turista.panel'));
    }

    public function test_una_agencia_no_puede_hacer_reservas(): void
    {
        $antes = Reserva::where('excursion_id', $this->saona()->id)->count();

        $this->actingAs($this->usuario('contacto@aventurasjarabacoa.test'))
            ->post('/excursiones/catamaran-a-isla-saona/reservar', [
                'fecha' => now()->addDays(5)->toDateString(),
                'cantidad_personas' => 2,
            ])->assertRedirect(route('agencia.panel'));

        $this->assertSame($antes, Reserva::where('excursion_id', $this->saona()->id)->count()); // no se agregó ninguna
    }

    public function test_la_fecha_debe_ser_futura_y_las_personas_validas(): void
    {
        $this->actingAs($this->usuario('maria@correo.test'))
            ->post('/excursiones/catamaran-a-isla-saona/reservar', [
                'fecha' => now()->subDay()->toDateString(),
                'cantidad_personas' => 0,
            ])->assertSessionHasErrors(['fecha', 'cantidad_personas']);
    }

    public function test_no_se_repite_la_misma_reserva_para_la_misma_fecha(): void
    {
        Notification::fake();
        $maria = $this->usuario('maria@correo.test');
        $datos = ['fecha' => now()->addDays(20)->toDateString(), 'cantidad_personas' => 2];

        $this->actingAs($maria)->post('/excursiones/catamaran-a-isla-saona/reservar', $datos);
        $this->actingAs($maria)->post('/excursiones/catamaran-a-isla-saona/reservar', $datos)->assertSessionHas('error');

        $this->assertSame(1, Reserva::where('usuario_id', $maria->id)->where('excursion_id', $this->saona()->id)
            ->whereDate('fecha', $datos['fecha'])->count());
    }

    public function test_no_se_reserva_una_excursion_oculta(): void
    {
        $this->saona()->update(['disponible' => false]);

        $this->actingAs($this->usuario('maria@correo.test'))
            ->post('/excursiones/catamaran-a-isla-saona/reservar', [
                'fecha' => now()->addDays(5)->toDateString(),
                'cantidad_personas' => 2,
            ])->assertNotFound();
    }

    // ---------- El turista cancela ----------

    public function test_el_turista_cancela_su_reserva(): void
    {
        $maria = $this->usuario('maria@correo.test');
        $reserva = $maria->reservas()->where('estado', 'pendiente')->firstOrFail();

        $this->actingAs($maria)->patch("/mi-cuenta/reservas/{$reserva->id}/cancelar")->assertSessionHas('exito');

        $this->assertSame('cancelada', $reserva->fresh()->estado);
    }

    public function test_un_turista_no_puede_cancelar_la_reserva_de_otro(): void
    {
        $deJohn = $this->usuario('john@correo.test')->reservas()->where('estado', 'pendiente')->firstOrFail();

        $this->actingAs($this->usuario('maria@correo.test'))
            ->patch("/mi-cuenta/reservas/{$deJohn->id}/cancelar")
            ->assertForbidden();

        $this->assertSame('pendiente', $deJohn->fresh()->estado);
    }

    // ---------- La agencia confirma o rechaza ----------

    public function test_la_agencia_confirma_una_reserva_y_el_turista_recibe_un_correo(): void
    {
        Notification::fake();
        $reserva = Reserva::where('excursion_id', $this->saona()->id)->where('estado', 'pendiente')->firstOrFail(); // pendiente, de John

        $this->actingAs($this->usuario('info@brisatropical.test'))
            ->patch("/agencia/reservas/{$reserva->id}/confirmar")
            ->assertSessionHas('exito');

        $this->assertSame('confirmada', $reserva->fresh()->estado);
        Notification::assertSentTo($this->usuario('john@correo.test'), EstadoReservaNotification::class);
    }

    public function test_la_agencia_rechaza_una_reserva(): void
    {
        Notification::fake();
        $reserva = Reserva::where('excursion_id', $this->saona()->id)->where('estado', 'pendiente')->firstOrFail();

        $this->actingAs($this->usuario('info@brisatropical.test'))
            ->patch("/agencia/reservas/{$reserva->id}/rechazar");

        $this->assertSame('cancelada', $reserva->fresh()->estado);
    }

    public function test_una_reserva_ya_respondida_no_se_puede_cambiar(): void
    {
        $confirmada = Reserva::where('estado', 'confirmada')->firstOrFail(); // Recorrido cultural (Brisa)

        $this->actingAs($this->usuario('info@brisatropical.test'))
            ->patch("/agencia/reservas/{$confirmada->id}/rechazar")
            ->assertSessionHas('error');

        $this->assertSame('confirmada', $confirmada->fresh()->estado);
    }

    public function test_una_agencia_no_puede_tocar_reservas_de_otra(): void
    {
        $deBrisa = Reserva::where('excursion_id', $this->saona()->id)->where('estado', 'pendiente')->firstOrFail();
        $this->actingAs($this->usuario('contacto@aventurasjarabacoa.test'));

        $this->get("/agencia/reservas/{$deBrisa->id}")->assertForbidden();
        $this->patch("/agencia/reservas/{$deBrisa->id}/confirmar")->assertForbidden();
        $this->assertSame('pendiente', $deBrisa->fresh()->estado);
    }

    public function test_la_agencia_ve_sus_reservas_filtradas_por_estado(): void
    {
        $this->actingAs($this->usuario('info@brisatropical.test'));

        $this->get('/agencia/reservas?estado=pendiente')
            ->assertOk()
            ->assertSee('Catamarán a isla Saona')
            ->assertDontSee('Recorrido cultural por Santo Domingo');

        $reserva = Reserva::where('excursion_id', $this->saona()->id)->where('estado', 'pendiente')->firstOrFail();
        $this->get("/agencia/reservas/{$reserva->id}")->assertOk()->assertSee('John Smith')->assertSee('RD$ 11,800');
    }

    // ---------- El administrador supervisa ----------

    public function test_el_administrador_ve_todas_las_reservas_y_otros_no(): void
    {
        $this->actingAs($this->usuario('admin@explorard.test'))
            ->get('/admin/reservas')
            ->assertOk()
            ->assertSee('Rafting en el río Yaque del Norte')
            ->assertSee('Catamarán a isla Saona');

        $this->actingAs($this->usuario('info@brisatropical.test'))
            ->get('/admin/reservas')
            ->assertRedirect(route('agencia.panel'));
    }
}
