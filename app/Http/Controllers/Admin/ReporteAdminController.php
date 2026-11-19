<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\GenerarReporte;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * «Reporte» del panel del administrador: muestra el reporte de estadísticas
 * que crea Python (python/reporte.py) y permite generarlo de nuevo con un botón.
 */
class ReporteAdminController extends Controller
{
    private function archivo(): string
    {
        return GenerarReporte::carpeta().DIRECTORY_SEPARATOR.'reporte.html';
    }

    public function index()
    {
        $existe = File::exists($this->archivo());
        $fecha = $existe ? \Illuminate\Support\Carbon::createFromTimestamp(File::lastModified($this->archivo()))->timezone(config('app.timezone')) : null;

        return view('admin.reporte', compact('existe', 'fecha'));
    }

    /** Botón «Generar reporte»: ejecuta el mismo comando que «php artisan explora:reporte». */
    public function generar()
    {
        try {
            $codigo = Artisan::call('explora:reporte');
        } catch (\Throwable $e) {
            // Algunos hostings gratuitos no dejan ejecutar programas (como Python)
            report($e);

            return back()->with('error', 'Este servidor no permite ejecutar Python, así que el reporte no se puede crear aquí. Créalo en la computadora con «php artisan explora:reporte».');
        }

        if ($codigo !== 0) {
            report(new \RuntimeException('Falló explora:reporte: '.Artisan::output()));

            return back()->with('error', 'No se pudo crear el reporte. Revisa que Python y matplotlib estén instalados (ver python/LEEME.md).');
        }

        return back()->with('exito', 'Reporte actualizado con los datos de este momento.');
    }

    /** La página del reporte (se muestra dentro del panel y se puede abrir aparte para imprimir). */
    public function ver()
    {
        abort_unless(File::exists($this->archivo()), 404);

        return response(File::get($this->archivo()), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
