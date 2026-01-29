<?php

namespace Database\Seeders;

use App\Models\Debt;
use App\Models\DebtType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DebtSeeder extends Seeder
{
    public function run(): void
    {
        $types = DebtType::all()->keyBy('name');
        $year = Carbon::now()->year;

        $debts = [
            [
                'debt_type_id' => $types['Cartão']->id ?? null,
                'name' => 'Nubank Crédito',
                'creditor' => 'Nubank',
                'total_amount' => 4200,
                'monthly_minimum' => 320,
                'due_day' => 12,
                'status' => 'active',
            ],
            [
                'debt_type_id' => $types['Empréstimo']->id ?? null,
                'name' => 'Empréstimo Banco X',
                'creditor' => 'Banco X',
                'total_amount' => 12000,
                'monthly_minimum' => 650,
                'due_day' => 18,
                'status' => 'active',
            ],
            [
                'debt_type_id' => $types['Acordo']->id ?? null,
                'name' => 'Acordo Lojas Y',
                'creditor' => 'Lojas Y',
                'total_amount' => 2600,
                'monthly_minimum' => 210,
                'due_day' => 6,
                'status' => 'renegotiating',
            ],
            [
                'debt_type_id' => $types['Financiamento']->id ?? null,
                'name' => 'Financiamento Carro',
                'creditor' => 'Financeira Z',
                'total_amount' => 28000,
                'monthly_minimum' => 780,
                'due_day' => 25,
                'status' => 'active',
            ],
        ];

        foreach ($debts as $debt) {
            Debt::create([
                ...$debt,
                'start_date' => Carbon::create($year, 1, 5),
            ]);
        }
    }
}
