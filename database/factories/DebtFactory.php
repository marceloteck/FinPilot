<?php

namespace Database\Factories;

use App\Models\Debt;
use App\Models\DebtType;
use Illuminate\Database\Eloquent\Factories\Factory;

class DebtFactory extends Factory
{
    protected $model = Debt::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'debt_type_id' => DebtType::factory(),
            'name' => $this->faker->company() . ' Dívida',
            'creditor' => $this->faker->company(),
            'total_amount' => $this->faker->randomFloat(2, 1000, 20000),
            'monthly_minimum' => $this->faker->randomFloat(2, 50, 1000),
            'interest_rate' => $this->faker->randomFloat(2, 1, 10),
            'due_day' => $this->faker->numberBetween(1, 28),
            'start_date' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'end_date' => null,
            'status' => 'active',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
