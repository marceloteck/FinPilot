<?php

namespace Database\Factories;

use App\Models\DebtType;
use Illuminate\Database\Eloquent\Factories\Factory;

class DebtTypeFactory extends Factory
{
    protected $model = DebtType::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => $this->faker->randomElement(['Cartão', 'Empréstimo', 'Financiamento', 'Acordo', 'Boleto', 'Outros']),
        ];
    }
}
