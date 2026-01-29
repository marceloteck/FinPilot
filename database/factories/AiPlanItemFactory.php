<?php

namespace Database\Factories;

use App\Models\AiPlan;
use App\Models\AiPlanItem;
use App\Models\Debt;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiPlanItemFactory extends Factory
{
    protected $model = AiPlanItem::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'ai_plan_id' => AiPlan::factory(),
            'debt_id' => Debt::factory(),
            'bucket' => 'mandatory',
            'suggested_amount' => $this->faker->randomFloat(2, 50, 500),
            'confirmed_amount' => null,
            'reason_json' => ['reasons' => ['Teste'], 'score' => 80],
        ];
    }
}
