<?php

namespace Database\Factories;

use App\Models\Status;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusFactory extends Factory
{
    protected $model = Status::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => $this->faker->randomElement(['Confirmado', 'Revisar', 'Pendente']),
        ];
    }
}
