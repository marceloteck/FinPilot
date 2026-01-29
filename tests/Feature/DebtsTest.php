<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\DebtType;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DebtsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2024, 6, 10));
    }

    public function test_debts_index_summary_with_income(): void
    {
        $type = DebtType::factory()->create();
        Debt::factory()->create([
            'debt_type_id' => $type->id,
            'monthly_minimum' => 200,
            'status' => 'active',
        ]);

        Transaction::factory()->create([
            'amount' => 1000,
            'date' => '2024-06-05',
        ]);

        $this->get('/debts?active_year=2024&active_month=06')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Pages/Debts')
                ->where('summary.income', 1000.0)
                ->where('summary.total_minimum', 200.0)
            );
    }

    public function test_debts_index_handles_zero_income(): void
    {
        $type = DebtType::factory()->create();
        Debt::factory()->create([
            'debt_type_id' => $type->id,
            'monthly_minimum' => 150,
            'status' => 'active',
        ]);

        $this->get('/debts?active_year=2024&active_month=06')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Pages/Debts')
                ->where('summary.commitment_percent', null)
            );
    }

    public function test_debt_crud(): void
    {
        $type = DebtType::factory()->create();

        $payload = [
            'debt_type_id' => $type->id,
            'name' => 'Cartão Teste',
            'creditor' => 'Banco',
            'total_amount' => 2000,
            'monthly_minimum' => 200,
            'interest_rate' => 5,
            'due_day' => 10,
            'status' => 'active',
            'notes' => 'Obs',
        ];

        $this->post('/debts', $payload)->assertRedirect();
        $this->assertDatabaseHas('debts', ['name' => 'Cartão Teste']);

        $debt = Debt::firstOrFail();

        $this->put("/debts/{$debt->id}", [
            ...$payload,
            'name' => 'Cartão Atualizado',
        ])->assertRedirect();

        $this->assertDatabaseHas('debts', ['name' => 'Cartão Atualizado']);

        $this->delete("/debts/{$debt->id}")->assertRedirect();
        $this->assertDatabaseMissing('debts', ['id' => $debt->id]);
    }
}
