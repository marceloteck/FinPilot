<?php

namespace Tests\Unit;

use App\Models\Debt;
use App\Models\DebtType;
use App\Models\Transaction;
use App\Services\AI\PaymentPlanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentPlanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2024, 4, 10));
    }

    public function test_generates_summary_and_reasons(): void
    {
        $type = DebtType::factory()->create(['name' => 'Cartão']);
        $debt = Debt::factory()->create([
            'debt_type_id' => $type->id,
            'monthly_minimum' => 300,
            'due_day' => 12,
            'status' => 'active',
        ]);

        Transaction::factory()->create([
            'amount' => 2000,
            'date' => '2024-04-05',
        ]);

        $service = new PaymentPlanService();
        $result = $service->generate(2024, '04', 'avalanche', null);

        $this->assertSame(2000.0, $result['summary']['income']);
        $this->assertSame(300.0, $result['summary']['minimum']);
        $this->assertArrayHasKey('reasons', $result['mandatory'][0]['reason_json']);
        $this->assertSame($debt->id, $result['mandatory'][0]['debt']->id);
    }

    public function test_snowball_orders_by_total_amount(): void
    {
        $type = DebtType::factory()->create(['name' => 'Cartão']);
        $small = Debt::factory()->create([
            'debt_type_id' => $type->id,
            'total_amount' => 1000,
            'monthly_minimum' => 100,
        ]);
        $large = Debt::factory()->create([
            'debt_type_id' => $type->id,
            'total_amount' => 5000,
            'monthly_minimum' => 100,
        ]);

        Transaction::factory()->create([
            'amount' => 3000,
            'date' => '2024-04-05',
        ]);

        $service = new PaymentPlanService();
        $result = $service->generate(2024, '04', 'snowball', null);

        $firstPriority = $result['priority'][0]['debt'];
        $this->assertSame($small->id, $firstPriority->id);
        $this->assertNotSame($large->id, $firstPriority->id);
    }
}
