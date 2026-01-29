<?php

namespace Database\Seeders;

use App\Models\SavedView;
use App\Models\Status;
use Illuminate\Database\Seeder;

class ViewSeeder extends Seeder
{
    public function run(): void
    {
        $baseConfig = [
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
        ];

        SavedView::create([
            'entity' => 'transactions',
            'name' => 'Este mês',
            'is_default' => true,
            'config_json' => $baseConfig,
        ]);

        $reviewConfig = $baseConfig;
        $reviewStatus = Status::where('name', 'Revisar')->first();
        $reviewConfig['filters']['status_id'] = $reviewStatus?->id;

        SavedView::create([
            'entity' => 'transactions',
            'name' => 'Revisar',
            'is_default' => false,
            'config_json' => $reviewConfig,
        ]);

        SavedView::create([
            'entity' => 'transactions',
            'name' => 'Tudo (ano)',
            'is_default' => false,
            'config_json' => $baseConfig,
        ]);
    }
}
