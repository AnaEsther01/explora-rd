<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Hosting sin terminal (InfinityFree): con FOTOS_EN_PUBLIC=true las fotos que suben
 * las agencias se guardan directamente en public/storage (sin «storage:link»).
 */
class FotosEnPublicTest extends TestCase
{
    public function test_por_defecto_las_fotos_van_a_storage(): void
    {
        $this->assertSame(storage_path('app/public'), config('filesystems.disks.public.root'));
    }

    public function test_con_la_opcion_activa_van_a_public_storage(): void
    {
        putenv('FOTOS_EN_PUBLIC=true');
        $_ENV['FOTOS_EN_PUBLIC'] = 'true';
        $_SERVER['FOTOS_EN_PUBLIC'] = 'true';

        try {
            $config = require config_path('filesystems.php');
            $this->assertSame(public_path('storage'), $config['disks']['public']['root']);
        } finally {
            putenv('FOTOS_EN_PUBLIC');
            unset($_ENV['FOTOS_EN_PUBLIC'], $_SERVER['FOTOS_EN_PUBLIC']);
        }
    }
}
