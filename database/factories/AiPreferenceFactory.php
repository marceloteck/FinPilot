<?php

namespace Database\Factories;

use App\Models\AiPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiPreferenceFactory extends Factory
{
    protected $model = AiPreference::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'payoff_strategy' => $this->faker->randomElement(['avalanche', 'snowball']),
            'risk_tolerance' => 'normal',
            'weights_json' => null,
        ];
    }
}
