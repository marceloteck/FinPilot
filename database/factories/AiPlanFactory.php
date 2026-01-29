<?php

namespace Database\Factories;

use App\Models\AiPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiPlanFactory extends Factory
{
    protected $model = AiPlan::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'year' => now()->year,
            'month' => now()->month,
            'status' => 'draft',
            'summary_json' => [
                'income' => 3000,
                'expenses' => 1500,
                'minimum' => 500,
                'available' => 2500,
                'overflow' => 0,
                'strategy' => 'avalanche',
            ],
        ];
    }
}
