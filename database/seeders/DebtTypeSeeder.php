<?php

namespace Database\Seeders;

use App\Models\DebtType;
use Illuminate\Database\Seeder;

class DebtTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Cartão', 'Empréstimo', 'Financiamento', 'Acordo', 'Boleto', 'Outros'];

        foreach ($types as $name) {
            DebtType::create(['name' => $name]);
        }
    }
}
