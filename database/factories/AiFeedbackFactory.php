<?php

namespace Database\Factories;

use App\Models\AiFeedback;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiFeedbackFactory extends Factory
{
    protected $model = AiFeedback::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'year' => now()->year,
            'month' => now()->month,
            'action' => 'confirm_plan',
            'payload_json' => ['ai_plan_id' => 1],
        ];
    }
}
