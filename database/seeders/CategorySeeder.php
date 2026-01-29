<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Renda',
            'Mercado',
            'Transporte',
            'Moradia',
            'Assinaturas',
            'Lazer',
            'Saúde',
            'Outros',
        ];

        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }
}
