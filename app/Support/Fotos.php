<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Ayuda para guardar y borrar las fotos que suben las agencias.
 *
 * Las fotos se guardan en storage/app/public/<carpeta> y se ven en la página
 * gracias al enlace public/storage (se crea con «php artisan storage:link»).
 * En la base de datos guardamos la ruta empezando por «storage/...»,
 * igual que las fotos de ejemplo empiezan por «img/...».
 */
class Fotos
{
    /** Guarda una foto subida y devuelve su ruta (ej.: storage/excursiones/abc.jpg). */
    public static function guardar(UploadedFile $archivo, string $carpeta): string
    {
        $nombre = Str::random(20).'.'.$archivo->extension();

        return 'storage/'.$archivo->storeAs($carpeta, $nombre, 'public');
    }

    /** Borra una foto subida. Las fotos de ejemplo (img/...) nunca se borran. */
    public static function borrar(?string $ruta): void
    {
        if ($ruta && str_starts_with($ruta, 'storage/')) {
            Storage::disk('public')->delete(substr($ruta, strlen('storage/')));
        }
    }
}
