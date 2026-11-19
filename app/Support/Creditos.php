<?php

namespace App\Support;

/**
 * Lee public/img/CREDITOS.txt para saber quién tomó cada foto.
 *
 * Cada línea de ese archivo tiene: ruta de la foto, autor y enlace. Por ejemplo:
 *   img/destinos/jarabacoa.jpg    Eriel Ezequiel Reyes Saviñon    https://unsplash.com/photos/...
 *
 * Así la galería puede mostrar «Foto: Autor (Unsplash)» sin escribir los créditos dos veces.
 */
class Creditos
{
    private static ?array $lista = null;

    /** Devuelve ['autor' => ..., 'fuente' => 'Unsplash', 'url' => ...] o null si no está anotada. */
    public static function de(?string $ruta): ?array
    {
        if (! $ruta) {
            return null;
        }

        return self::todos()[ltrim($ruta, '/')] ?? null;
    }

    private static function todos(): array
    {
        if (self::$lista !== null) {
            return self::$lista;
        }

        self::$lista = [];
        $archivo = public_path('img/CREDITOS.txt');
        if (! is_file($archivo)) {
            return self::$lista;
        }

        foreach (file($archivo, FILE_IGNORE_NEW_LINES) as $linea) {
            // «img/… (dos o más espacios) Autor (dos o más espacios) https://…»
            if (preg_match('#^(img/\S+)\s{2,}(.+?)\s{2,}(https?://\S+)\s*$#u', $linea, $m)) {
                $host = parse_url($m[3], PHP_URL_HOST) ?: '';
                self::$lista[$m[1]] = [
                    'autor' => trim($m[2]),
                    'fuente' => str_contains($host, 'pexels') ? 'Pexels' : (str_contains($host, 'unsplash') ? 'Unsplash' : $host),
                    'url' => $m[3],
                ];
            }
        }

        return self::$lista;
    }
}
