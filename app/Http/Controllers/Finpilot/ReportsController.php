<?php

namespace App\Http\Controllers\Finpilot;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\Transaction;
use App\Support\PeriodContext;
use App\Support\SubscriptionGate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id;
        $activeYear = (int) $request->input('active_year', Carbon::now()->year);
        $period = PeriodContext::current($activeYear, 'all');

        if (!SubscriptionGate::reportsEnabled($userId)) {
            return redirect()->back()->with('warning', 'Seu plano não permite acessar relatórios.');
        }

        $incomeTotal = Transaction::query()
            ->forUser($userId)
            ->whereBetween('date', [$period['start_date'], $period['end_date']])
            ->where('amount', '>', 0)
            ->sum('amount');

        $expensesTotal = Transaction::query()
            ->forUser($userId)
            ->whereBetween('date', [$period['start_date'], $period['end_date']])
            ->where('amount', '<', 0)
            ->sum('amount');

        $debtMinimumTotal = Debt::forUser($userId)
            ->whereIn('status', ['active', 'renegotiating'])
            ->sum('monthly_minimum');

        $monthly = [];
        for ($month = 1; $month <= 12; $month += 1) {
            $start = Carbon::createFromDate($activeYear, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $income = Transaction::query()
                ->forUser($userId)
                ->whereBetween('date', [$start, $end])
                ->where('amount', '>', 0)
                ->sum('amount');

            $expenses = Transaction::query()
                ->forUser($userId)
                ->whereBetween('date', [$start, $end])
                ->where('amount', '<', 0)
                ->sum('amount');

            $monthly[] = [
                'month' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
                'income' => (float) $income,
                'expenses' => (float) abs($expenses),
                'debts' => (float) $debtMinimumTotal,
                'balance' => (float) ($income + $expenses - $debtMinimumTotal),
            ];
        }

        return Inertia::render('Pages/Reports', [
            'summary' => [
                'income' => (float) $incomeTotal,
                'expenses' => (float) abs($expensesTotal),
                'debts' => (float) $debtMinimumTotal,
                'balance' => (float) ($incomeTotal + $expensesTotal - $debtMinimumTotal),
            ],
            'monthly' => $monthly,
            'period' => [
                'activeYear' => $period['year'],
                'activeMonth' => 'all',
            ],
        ]);
    }
}
