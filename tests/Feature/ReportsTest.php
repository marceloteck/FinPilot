<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\DebtType;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2024, 3, 10));
    }

    public function test_reports_summary(): void
    {
        $type = DebtType::factory()->create();
        Debt::factory()->create([
            'debt_type_id' => $type->id,
            'monthly_minimum' => 150,
        ]);

        Transaction::factory()->create([
            'amount' => 2000,
            'date' => '2024-03-05',
        ]);

        $this->get('/reports?active_year=2024')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Pages/Reports')
                ->where('summary.income', 2000.0)
                ->where('summary.debts', 150.0)
            );
    }
}
