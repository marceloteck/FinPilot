<?php

namespace Database\Factories;

use App\Models\SavedView;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavedViewFactory extends Factory
{
    protected $model = SavedView::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'entity' => 'transactions',
            'name' => 'View ' . $this->faker->word(),
            'is_default' => false,
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
    }
}
