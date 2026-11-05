<?php

namespace Database\Factories;

use App\Models\Docente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Docente>
 */
class DocenteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cod_empleado' => fake()->unique()->numberBetween(2000, 99999),
            'nombre_empleado' => fake()->firstName(),
            'apellidos_empleado' => fake()->lastName().' '.fake()->lastName(),
        ];
    }
}