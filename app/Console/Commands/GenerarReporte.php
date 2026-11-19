<?php

namespace App\Console\Commands;

use App\Support\Estadisticas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/**
 * Crea el reporte de Explora RD en dos pasos:
 *   1. Laravel calcula los números y los guarda en storage/app/reportes/datos.json
 *   2. Python (python/reporte.py) lee ese archivo, dibuja las gráficas con matplotlib
 *      y crea la página storage/app/reportes/reporte.html
 *
 * Uso:  php artisan explora:reporte
 *       php artisan explora:reporte --solo-datos   (solo el paso 1, sin Python)
 *
 * El reporte se guarda en storage/ (privado), no en public/: solo el administrador lo puede ver.
 */
class GenerarReporte extends Command
{
    protected $signature = 'explora:reporte {--solo-datos : Solo guarda datos.json, sin ejecutar Python}';

    protected $description = 'Crea el reporte de estadísticas de Explora RD (Laravel + Python)';

    public static function carpeta(): string
    {
        return storage_path('app/reportes');
    }

    public function handle(): int
    {
        File::ensureDirectoryExists(self::carpeta());
        $datos = self::carpeta().DIRECTORY_SEPARATOR.'datos.json';
        $reporte = self::carpeta().DIRECTORY_SEPARATOR.'reporte.html';

        // Paso 1: Laravel prepara los datos
        File::put($datos, json_encode(Estadisticas::calcular(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->info('1/2 Datos guardados en '.$datos);

        if ($this->option('solo-datos')) {
            return self::SUCCESS;
        }

        // Paso 2: Python dibuja las gráficas y crea la página del reporte
        // MPLCONFIGDIR: carpeta donde matplotlib guarda su configuración y su lista de letras.
        // Sin esto falla cuando el programa lo ejecuta la página web (no encuentra la carpeta del usuario).
        $configuracionMatplotlib = self::carpeta().DIRECTORY_SEPARATOR.'.matplotlib';
        File::ensureDirectoryExists($configuracionMatplotlib);

        $resultado = Process::path(base_path())
            ->env(['MPLCONFIGDIR' => $configuracionMatplotlib])
            ->timeout(120)
            ->run([config('explora.python'), base_path('python/reporte.py'), '--datos', $datos, '--salida', $reporte]);

        if ($resultado->failed()) {
            $this->error('Python no pudo crear el reporte.');
            $this->line(trim($resultado->errorOutput()) ?: trim($resultado->output()));
            $this->line('Revisa que Python y matplotlib estén instalados (ver python/LEEME.md).');

            return self::FAILURE;
        }

        $this->info('2/2 '.trim($resultado->output()));

        return self::SUCCESS;
    }
}
