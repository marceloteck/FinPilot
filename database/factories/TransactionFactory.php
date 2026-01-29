<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Status;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'date' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'description' => $this->faker->sentence(3),
            'amount' => $this->faker->randomFloat(2, -500, 2000),
            'account_name' => $this->faker->word(),
            'category_id' => Category::factory(),
            'status_id' => Status::factory(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
