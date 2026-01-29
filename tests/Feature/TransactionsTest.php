<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Status;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TransactionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2024, 5, 20));
    }

    public function test_index_filters_by_period(): void
    {
        $category = Category::factory()->create();
        $status = Status::factory()->create();

        Transaction::factory()->create([
            'date' => '2024-05-10',
            'description' => 'Conta maio',
            'category_id' => $category->id,
            'status_id' => $status->id,
        ]);

        Transaction::factory()->create([
            'date' => '2023-05-10',
            'description' => 'Conta passada',
            'category_id' => $category->id,
            'status_id' => $status->id,
        ]);

        $this->get('/transactions?active_year=2024&active_month=05')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Pages/Transactions')
                ->has('transactions.data', 1)
                ->where('transactions.data.0.description', 'Conta maio')
            );
    }

    public function test_store_update_delete_transaction(): void
    {
        $category = Category::factory()->create();
        $status = Status::factory()->create();

        $payload = [
            'date' => '2024-05-15',
            'description' => 'Nova transação',
            'amount' => 120.50,
            'account_name' => 'Conta teste',
            'category_id' => $category->id,
            'status_id' => $status->id,
            'notes' => 'Observação',
        ];

        $this->post('/transactions', $payload)->assertRedirect();
        $this->assertDatabaseHas('transactions', ['description' => 'Nova transação']);

        $transaction = Transaction::firstOrFail();

        $this->put("/transactions/{$transaction->id}", [
            ...$payload,
            'description' => 'Atualizada',
        ])->assertRedirect();

        $this->assertDatabaseHas('transactions', ['description' => 'Atualizada']);

        $this->delete("/transactions/{$transaction->id}")->assertRedirect();
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_filters_by_search_category_and_status(): void
    {
        $category = Category::factory()->create(['name' => 'Mercado']);
        $status = Status::factory()->create(['name' => 'Revisar']);

        Transaction::factory()->create([
            'description' => 'Compra mercado',
            'category_id' => $category->id,
            'status_id' => $status->id,
            'date' => '2024-05-03',
        ]);

        Transaction::factory()->create([
            'description' => 'Outro gasto',
            'date' => '2024-05-04',
        ]);

        $this->get("/transactions?active_year=2024&active_month=05&search=mercado&category_id={$category->id}&status_id={$status->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Pages/Transactions')
                ->has('transactions.data', 1)
                ->where('transactions.data.0.description', 'Compra mercado')
            );
    }
}
