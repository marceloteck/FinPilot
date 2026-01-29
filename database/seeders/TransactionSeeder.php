<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Status;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $statuses = Status::all();
        $year = Carbon::now()->year;

        $data = [
            ['date' => Carbon::create($year, 1, 5), 'description' => 'Salário', 'amount' => 3500, 'category' => 'Renda', 'status' => 'Confirmado'],
            ['date' => Carbon::create($year, 1, 10), 'description' => 'Supermercado', 'amount' => -420.40, 'category' => 'Mercado', 'status' => 'Confirmado'],
            ['date' => Carbon::create($year, 2, 3), 'description' => 'Combustível', 'amount' => -180, 'category' => 'Transporte', 'status' => 'Revisar'],
            ['date' => Carbon::create($year, 2, 12), 'description' => 'Plano de saúde', 'amount' => -320, 'category' => 'Saúde', 'status' => 'Confirmado'],
            ['date' => Carbon::create($year, 3, 8), 'description' => 'Aluguel', 'amount' => -1200, 'category' => 'Moradia', 'status' => 'Confirmado'],
            ['date' => Carbon::create($year, 3, 16), 'description' => 'Assinatura streaming', 'amount' => -39.9, 'category' => 'Assinaturas', 'status' => 'Pendente'],
            ['date' => Carbon::create($year, 4, 2), 'description' => 'Freelance', 'amount' => 950, 'category' => 'Renda', 'status' => 'Confirmado'],
            ['date' => Carbon::create($year, 4, 20), 'description' => 'Cinema', 'amount' => -65, 'category' => 'Lazer', 'status' => 'Confirmado'],
            ['date' => Carbon::create($year, 5, 11), 'description' => 'Mercado do mês', 'amount' => -520.15, 'category' => 'Mercado', 'status' => 'Confirmado'],
            ['date' => Carbon::create($year, 5, 25), 'description' => 'Uber', 'amount' => -48, 'category' => 'Transporte', 'status' => 'Revisar'],
            ['date' => Carbon::create($year, 6, 4), 'description' => 'Conta de luz', 'amount' => -210, 'category' => 'Moradia', 'status' => 'Confirmado'],
            ['date' => Carbon::create($year, 6, 15), 'description' => 'Venda extra', 'amount' => 300, 'category' => 'Renda', 'status' => 'Confirmado'],
            ['date' => Carbon::create($year, 7, 7), 'description' => 'Academia', 'amount' => -99.9, 'category' => 'Saúde', 'status' => 'Pendente'],
            ['date' => Carbon::create($year, 8, 9), 'description' => 'Almoço fora', 'amount' => -58.7, 'category' => 'Lazer', 'status' => 'Confirmado'],
            ['date' => Carbon::create($year, 9, 1), 'description' => 'Reserva', 'amount' => 420, 'category' => 'Outros', 'status' => 'Confirmado'],
        ];

        foreach ($data as $item) {
            $category = $categories->firstWhere('name', $item['category']);
            $status = $statuses->firstWhere('name', $item['status']);

            Transaction::create([
                'date' => $item['date'],
                'description' => $item['description'],
                'amount' => $item['amount'],
                'category_id' => $category?->id,
                'status_id' => $status?->id,
                'account_name' => 'Conta principal',
            ]);
        }
    }
}
