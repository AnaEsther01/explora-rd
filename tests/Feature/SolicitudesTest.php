<?php

namespace Tests\Feature;

use App\Models\Solicitud;
use App\Models\Usuario;
use App\Notifications\NuevaSolicitudNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Pruebas de las páginas de Asesorías y Renta de vehículos
 * y de las solicitudes que hacen los turistas.
 */
class SolicitudesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function usuario(string $correo): Usuario
    {
        return Usuario::where('correo', $correo)->firstOrFail();
    }

    private function datosVehiculo(array $cambios = []): array
    {
        return array_merge([
            'servicio' => 'vehiculo',
            'lugar' => 'Aeropuerto de Punta Cana',
            'fecha_inicio' => now()->addDays(5)->toDateString(),
            'fecha_fin' => now()->addDays(9)->toDateString(),
            'cantidad_personas' => 4,
            'tipo_vehiculo' => 'jeepeta',
            'telefono' => '809-555-1234',
            'mensaje' => 'Con asiento para niño.',
        ], $cambios);
    }

    public function test_las_paginas_de_servicios_se_abren_para_todos(): void
    {
        $this->get('/asesorias')->assertOk()
            ->assertSee('Organiza tu viaje con nuestra ayuda')
            ->assertSee('Inicia sesión para enviar');

        $this->get('/renta-de-vehiculos')->assertOk()
            ->assertSee('Muévete con libertad por la isla')
            ->assertSee('Jeepeta (SUV)')
            ->assertSee('Aeropuerto de Punta Cana');
    }

    public function test_el_formulario_llega_lleno_desde_el_buscador_de_la_portada(): void
    {
        $fecha = now()->addDays(15)->toDateString();

        $this->actingAs($this->usuario('maria@correo.test'))
            ->get('/asesorias?lugar=Samaná&fecha='.$fecha.'&personas=4')
            ->assertOk()
            ->assertSee('value="Samaná"', false)
            ->assertSee('value="'.$fecha.'"', false)
            ->assertSee('<option value="4" selected>', false)
            ->assertSee('Enviar solicitud');

        // Una fecha que ya pasó no se pone en el formulario
        $this->get('/asesorias?fecha=2020-01-01')->assertDontSee('value="2020-01-01"', false);
    }

    public function test_un_turista_pide_un_vehiculo_y_se_avisa_al_administrador(): void
    {
        Notification::fake();
        $maria = $this->usuario('maria@correo.test');

        $this->actingAs($maria)->post('/solicitudes', $this->datosVehiculo())
            ->assertRedirect(route('turista.panel'))
            ->assertSessionHas('exito');

        $solicitud = Solicitud::where('usuario_id', $maria->id)->where('servicio', 'vehiculo')->firstOrFail();
        $this->assertSame('pendiente', $solicitud->estado);
        $this->assertSame('jeepeta', $solicitud->tipo_vehiculo);

        Notification::assertSentTo($this->usuario('admin@explorard.test'), NuevaSolicitudNotification::class);

        // La ve en «Mi cuenta»
        $this->get('/mi-cuenta')->assertSee('Mis solicitudes')->assertSee('Renta de vehículo')->assertSee('Aeropuerto de Punta Cana');
    }

    public function test_la_asesoria_no_necesita_fecha_de_salida_ni_vehiculo(): void
    {
        $this->actingAs($this->usuario('maria@correo.test'))->post('/solicitudes', [
            'servicio' => 'asesoria',
            'lugar' => 'Jarabacoa',
            'fecha_inicio' => now()->addDays(20)->toDateString(),
            'cantidad_personas' => 2,
            'tipo_vehiculo' => 'minivan', // se ignora en una asesoría
            'telefono' => '809 555 0000',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('solicitudes', ['servicio' => 'asesoria', 'lugar' => 'Jarabacoa', 'fecha_fin' => null, 'tipo_vehiculo' => null]);
    }

    public function test_revisa_los_datos_del_formulario(): void
    {
        $maria = $this->usuario('maria@correo.test');

        $this->actingAs($maria)->post('/solicitudes', $this->datosVehiculo([
            'fecha_inicio' => now()->subDay()->toDateString(), // ya pasó
            'fecha_fin' => '',                                 // obligatoria para vehículos
            'tipo_vehiculo' => 'avion',                        // no existe
            'telefono' => 'llámame',                           // no es un teléfono
        ]))->assertSessionHasErrors(['fecha_inicio', 'fecha_fin', 'tipo_vehiculo', 'telefono']);

        // La devolución no puede ser antes de la recogida
        $this->post('/solicitudes', $this->datosVehiculo([
            'fecha_inicio' => now()->addDays(9)->toDateString(),
            'fecha_fin' => now()->addDays(5)->toDateString(),
        ]))->assertSessionHasErrors('fecha_fin');
    }

    public function test_solo_los_turistas_pueden_enviar_solicitudes(): void
    {
        $this->post('/solicitudes', $this->datosVehiculo())->assertRedirect('/login');

        // El middleware «rol» manda a la agencia a su propio panel, sin guardar nada
        $this->actingAs($this->usuario('info@brisatropical.test'))
            ->post('/solicitudes', $this->datosVehiculo())->assertRedirect(route('agencia.panel'));
        $this->assertDatabaseMissing('solicitudes', ['lugar' => 'Aeropuerto de Punta Cana', 'cantidad_personas' => 4]);

        $this->actingAs($this->usuario('info@brisatropical.test'))
            ->get('/renta-de-vehiculos')->assertSee('Las solicitudes las hacen las cuentas de turista.');
    }

    public function test_no_se_permiten_mas_de_tres_solicitudes_pendientes(): void
    {
        $maria = $this->usuario('maria@correo.test'); // ya tiene 1 pendiente (seeder)
        $this->actingAs($maria);

        $this->post('/solicitudes', $this->datosVehiculo())->assertSessionHas('exito');
        $this->post('/solicitudes', $this->datosVehiculo())->assertSessionHas('exito');
        $this->post('/solicitudes', $this->datosVehiculo())->assertSessionHas('error');

        $this->assertSame(3, $maria->solicitudes()->where('estado', 'pendiente')->count());
    }

    public function test_el_turista_cancela_solo_sus_solicitudes_pendientes(): void
    {
        $maria = $this->usuario('maria@correo.test');
        $john = $this->usuario('john@correo.test');
        $deMaria = $maria->solicitudes()->firstOrFail();
        $deJohn = $john->solicitudes()->firstOrFail(); // ya atendida

        // John no puede cancelar la de María
        $this->actingAs($john)->patch("/mi-cuenta/solicitudes/{$deMaria->id}/cancelar")->assertForbidden();

        // Una atendida ya no se cancela
        $this->patch("/mi-cuenta/solicitudes/{$deJohn->id}/cancelar")->assertSessionHas('error');

        $this->actingAs($maria)->patch("/mi-cuenta/solicitudes/{$deMaria->id}/cancelar")->assertSessionHas('exito');
        $this->assertSame('cancelada', $deMaria->fresh()->estado);
    }

    public function test_el_administrador_atiende_la_solicitud_y_el_turista_ve_la_respuesta(): void
    {
        $admin = $this->usuario('admin@explorard.test');
        $maria = $this->usuario('maria@correo.test');
        $solicitud = $maria->solicitudes()->firstOrFail();

        $this->actingAs($admin)->get('/admin/solicitudes')->assertOk()
            ->assertSee('Solicitudes de asesoría y renta de vehículos')
            ->assertSee($maria->nombre);

        $this->patch("/admin/solicitudes/{$solicitud->id}", [
            'estado' => 'atendida',
            'respuesta' => 'Te enviamos un plan de 5 días por WhatsApp.',
        ])->assertSessionHas('exito');

        $this->actingAs($maria)->get('/mi-cuenta')
            ->assertSee('Atendida')
            ->assertSee('Te enviamos un plan de 5 días por WhatsApp.');

        // Un turista no puede entrar a la página del administrador
        $this->get('/admin/solicitudes')->assertRedirect(route('turista.panel'));
    }

    public function test_las_paginas_de_servicios_se_traducen(): void
    {
        $this->get('/idioma/en');
        $this->get('/renta-de-vehiculos')->assertSee('Explore the island freely')->assertSee('With driver');

        $this->get('/idioma/fr');
        $this->get('/asesorias')->assertSee('Organisez votre voyage avec notre aide');
    }

    public function test_el_menu_y_el_sitemap_enlazan_las_paginas(): void
    {
        $this->get('/')->assertSee(route('servicios.asesorias'))->assertSee(route('servicios.vehiculos'));
        $this->get('/sitemap.xml')->assertSee(route('servicios.vehiculos'));
    }
}
