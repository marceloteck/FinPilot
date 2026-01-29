<?php

namespace Tests\Feature;

use App\Models\SavedView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_update_delete_view(): void
    {
        $payload = [
            'entity' => 'transactions',
            'name' => 'Minha visão',
            'config_json' => [
                'filters' => [
                    'search' => '',
                    'category_id' => null,
                    'status_id' => null,
                    'min_amount' => null,
                    'max_amount' => null,
                ],
                'sort' => ['by' => 'date', 'dir' => 'desc'],
                'columns' => [
                    'date' => true,
                    'description' => true,
                    'amount' => true,
                    'account_name' => true,
                    'category' => true,
                    'status' => true,
                    'notes' => false,
                ],
            ],
        ];

        $this->post('/views', $payload)->assertRedirect();
        $this->assertDatabaseHas('views', ['name' => 'Minha visão']);

        $view = SavedView::firstOrFail();

        $this->put("/views/{$view->id}", [
            'name' => 'Visão Atualizada',
            'config_json' => $payload['config_json'],
            'is_default' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('views', ['name' => 'Visão Atualizada']);

        $this->delete("/views/{$view->id}")->assertRedirect();
        $this->assertDatabaseMissing('views', ['id' => $view->id]);
    }
}
