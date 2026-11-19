<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Crea la cuenta de administrador de verdad (para la página publicada).
 * Uso:  php artisan explora:crear-admin
 * Pregunta el nombre, el correo y la contraseña (la contraseña no se ve al escribirla).
 */
class CrearAdministrador extends Command
{
    protected $signature = 'explora:crear-admin';

    protected $description = 'Crea una cuenta de administrador de Explora RD';

    public function handle(): int
    {
        $nombre = $this->ask('Nombre del administrador');
        $correo = strtolower((string) $this->ask('Correo electrónico'));
        $contrasena = $this->secret('Contraseña (mínimo 10 caracteres, con letras y números)');
        $repetir = $this->secret('Repite la contraseña');

        $validacion = Validator::make(
            ['nombre' => $nombre, 'correo' => $correo, 'contrasena' => $contrasena, 'contrasena_confirmation' => $repetir],
            [
                'nombre' => ['required', 'string', 'max:100'],
                'correo' => ['required', 'email', 'max:100', 'unique:usuarios,correo'],
                'contrasena' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
            ]
        );

        if ($validacion->fails()) {
            foreach ($validacion->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        Usuario::create([
            'nombre' => $nombre,
            'correo' => $correo,
            'contrasena' => $contrasena, // el modelo la cifra
            'rol' => 'administrador',
        ]);

        $this->info('Listo: se creó el administrador '.$correo.'. Ya puede entrar en /login.');

        return self::SUCCESS;
    }
}
