<?php

namespace Tests\Feature;

use App\Support\Estadisticas;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

/**
 * Pruebas del reporte de estadísticas (Laravel prepara los datos y Python dibuja las gráficas).
 * Las pruebas de Python están aparte: python/test_reporte.py
 */
class ReporteTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private string $carpetaTemporal;

    protected function setUp(): void
    {
        parent::setUp();
        // Usamos una carpeta temporal para no borrar el reporte de verdad
        $this->carpetaTemporal = sys_get_temp_dir().DIRECTORY_SEPARATOR.'explora-reporte-'.uniqid();
        File::ensureDirectoryExists($this->carpetaTemporal.'/app');
        $this->app->useStoragePath($this->carpetaTemporal);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->carpetaTemporal);
        parent::tearDown();
    }

    private function admin(): Usuario
    {
        return Usuario::where('correo', 'admin@explorard.test')->firstOrFail();
    }

    public function test_las_estadisticas_cuentan_bien(): void
    {
        $datos = Estadisticas::calcular();

        $this->assertSame(2, $datos['totales']['turistas']);
        $this->assertSame(12, $datos['totales']['destinos']);
        $this->assertSame(\App\Models\Reserva::count(), array_sum($datos['reservas_por_estado']));
        $this->assertCount(12, $datos['reservas_por_mes']);

        // La más reservada de los datos de prueba es el catamarán
        $this->assertSame('Catamarán a isla Saona', $datos['mas_reservadas'][0]['titulo']);
        // La mejor valorada tiene 5 estrellas
        $this->assertEquals(5.0, $datos['mejor_valoradas'][0]['promedio']);
        $this->assertSame(['Asesoría de viaje', 'Renta de vehículo'], array_keys($datos['solicitudes_por_servicio']));
    }

    public function test_las_canceladas_no_cuentan_en_los_ingresos(): void
    {
        $antes = Estadisticas::calcular()['totales']['ingresos_estimados'];

        \App\Models\Reserva::where('estado', 'confirmada')->first()->update(['estado' => 'cancelada']);

        $this->assertLessThan($antes, Estadisticas::calcular()['totales']['ingresos_estimados']);
    }

    public function test_el_comando_guarda_los_datos_en_json(): void
    {
        $this->artisan('explora:reporte --solo-datos')->assertSuccessful();

        $datos = json_decode(File::get(storage_path('app/reportes/datos.json')), true);
        $this->assertArrayHasKey('totales', $datos);
        $this->assertArrayHasKey('agencias', $datos);
    }

    public function test_el_administrador_genera_y_ve_el_reporte(): void
    {
        // Simulamos Python para que la prueba no dependa de que esté instalado
        Process::fake(function () {
            File::put(storage_path('app/reportes/reporte.html'), '<h1>Estadísticas de la plataforma</h1>');

            return Process::result('Reporte creado');
        });

        $this->actingAs($this->admin())->get('/admin/reporte')->assertOk()->assertSee('Generar reporte');

        $this->post('/admin/reporte')->assertSessionHas('exito');
        Process::assertRan(fn ($proceso) => str_contains(implode(' ', (array) $proceso->command), 'reporte.py')
            && isset($proceso->environment['MPLCONFIGDIR'])); // carpeta propia de matplotlib (ver GenerarReporte)

        $this->get('/admin/reporte')->assertSee('Actualizar reporte')->assertSee('marco-reporte', false);
        $this->get('/admin/reporte/ver')->assertOk()->assertSee('Estadísticas de la plataforma');
    }

    public function test_si_python_falla_se_avisa(): void
    {
        Process::fake(fn () => Process::result(errorOutput: 'No module named matplotlib', exitCode: 1));

        $this->actingAs($this->admin())->post('/admin/reporte')->assertSessionHas('error');
    }

    public function test_si_el_servidor_no_deja_ejecutar_python_se_avisa(): void
    {
        // Así se comporta un hosting gratuito que desactiva la ejecución de programas
        Process::fake(fn () => throw new \RuntimeException('proc_open está desactivado'));

        $this->actingAs($this->admin())->post('/admin/reporte')
            ->assertRedirect()
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'no permite ejecutar Python'));
    }

    public function test_solo_el_administrador_ve_el_reporte(): void
    {
        $this->get('/admin/reporte')->assertRedirect('/login');

        $turista = Usuario::where('correo', 'maria@correo.test')->firstOrFail();
        $this->actingAs($turista)->get('/admin/reporte/ver')->assertRedirect(route('turista.panel'));
        $this->actingAs($turista)->post('/admin/reporte')->assertRedirect(route('turista.panel'));
    }

    public function test_sin_reporte_la_pagina_lo_explica(): void
    {
        $this->actingAs($this->admin())->get('/admin/reporte')->assertSee('Todavía no hay reporte');
        $this->get('/admin/reporte/ver')->assertNotFound();
    }

    /** Prueba con Python de verdad (se salta si la computadora no tiene Python con matplotlib). */
    public function test_python_crea_el_reporte_de_verdad(): void
    {
        $revision = Process::run([config('explora.python'), '-c', 'import matplotlib']);
        if ($revision->failed()) {
            $this->markTestSkipped('Python con matplotlib no está instalado en esta computadora.');
        }

        $this->artisan('explora:reporte')->assertSuccessful();

        $html = File::get(storage_path('app/reportes/reporte.html'));
        $this->assertStringContainsString('Estadísticas de la plataforma', $html);
        $this->assertStringContainsString('Catamarán a isla Saona', $html);
        $this->assertGreaterThanOrEqual(4, substr_count($html, 'data:image/png;base64,'));
    }
}
