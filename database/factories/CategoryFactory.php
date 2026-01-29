<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => $this->faker->word(),
            'color' => null,
            'is_hidden' => false,
        ];
    }
}
