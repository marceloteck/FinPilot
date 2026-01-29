<?php

namespace Tests\Feature;

use App\Models\AiPlan;
use App\Models\AiPlanItem;
use App\Models\Debt;
use App\Models\DebtType;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2024, 7, 15));
    }

    public function test_generate_plan_creates_items(): void
    {
        $type = DebtType::factory()->create();
        Debt::factory()->count(2)->create([
            'debt_type_id' => $type->id,
            'monthly_minimum' => 200,
            'status' => 'active',
        ]);

        Transaction::factory()->create([
            'amount' => 3000,
            'date' => '2024-07-05',
        ]);

        $this->post('/ai/generate', [
            'active_year' => 2024,
            'active_month' => '07',
        ])->assertRedirect();

        $plan = AiPlan::first();
        $this->assertNotNull($plan);
        $this->assertDatabaseCount('ai_plan_items', 2);
    }

    public function test_confirm_plan_updates_status(): void
    {
        $plan = AiPlan::factory()->create(['status' => 'draft']);
        $item = AiPlanItem::factory()->create([
            'ai_plan_id' => $plan->id,
            'suggested_amount' => 200,
        ]);

        $this->post('/ai/confirm', [
            'ai_plan_id' => $plan->id,
            'items' => [
                ['id' => $item->id, 'confirmed_amount' => 150],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('ai_plans', ['id' => $plan->id, 'status' => 'confirmed']);
        $this->assertDatabaseHas('ai_plan_items', ['id' => $item->id, 'confirmed_amount' => 150]);
    }

    public function test_feedback_changes_strategy(): void
    {
        $this->post('/ai/feedback', [
            'action' => 'change_strategy',
            'payload' => [
                'strategy' => 'snowball',
                'year' => 2024,
                'month' => 7,
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('ai_preferences', ['payoff_strategy' => 'snowball']);
    }
}
