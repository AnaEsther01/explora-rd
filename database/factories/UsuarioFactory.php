<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Fabrica usuarios de prueba con datos inventados.
 *
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected static ?string $contrasena;

    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'contrasena' => static::$contrasena ??= Hash::make('password'),
            'telefono' => fake()->numerify('809-###-####'),
            'rol' => 'turista',
            'remember_token' => Str::random(10),
        ];
    }

    /** Un usuario con rol de agencia. */
    public function agencia(): static
    {
        return $this->state(fn () => [
            'rol' => 'agencia',
            'nombre_comercial' => fake()->company(),
            'descripcion' => fake()->paragraph(),
            'whatsapp' => fake()->numerify('1809#######'),
            'direccion' => fake()->address(),
        ]);
    }

    /** Un usuario con rol de administrador. */
    public function administrador(): static
    {
        return $this->state(fn () => ['rol' => 'administrador']);
    }
}
