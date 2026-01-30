<?php

namespace App\Http\Controllers\Finpilot;

use App\Http\Controllers\Controller;
use App\Models\AiPlan;
use App\Models\Debt;
use App\Models\Transaction;
use App\Support\PeriodContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    public function home(Request $request)
    {
        $userId = $request->user()?->id;
        $activeYear = (int) $request->input('active_year', Carbon::now()->year);
        $activeMonth = $request->input('active_month', Carbon::now()->format('m'));
        $period = PeriodContext::current($activeYear, $activeMonth);

        $income = Transaction::query()
            ->forUser($userId)
            ->whereBetween('date', [$period['start_date'], $period['end_date']])
            ->where('amount', '>', 0)
            ->sum('amount');

        $expenses = Transaction::query()
            ->forUser($userId)
            ->whereBetween('date', [$period['start_date'], $period['end_date']])
            ->where('amount', '<', 0)
            ->sum('amount');

        $debtMinimum = Debt::forUser($userId)
            ->whereIn('status', ['active', 'renegotiating'])
            ->sum('monthly_minimum');

        $balance = (float) $income - (float) abs($expenses) - (float) $debtMinimum;
        $commitmentPercent = $income > 0 ? round(($debtMinimum / $income) * 100, 1) : null;
        $commitmentStatus = $commitmentPercent === null
            ? 'Sem renda'
            : ($commitmentPercent < 40 ? 'Baixo' : ($commitmentPercent < 70 ? 'Médio' : 'Alto'));

        $debts = Debt::forUser($userId)
            ->with('type')
            ->whereIn('status', ['active', 'renegotiating'])
            ->orderBy('due_day')
            ->limit(3)
            ->get();

        $transactions = Transaction::query()
            ->forUser($userId)
            ->with(['category', 'status'])
            ->whereBetween('date', [$period['start_date'], $period['end_date']])
            ->orderByDesc('date')
            ->limit(3)
            ->get();

        $alerts = $debts->map(function (Debt $debt) {
            $today = Carbon::now();
            $dueDate = $today->copy()->day($debt->due_day);
            $diff = $today->diffInDays($dueDate, false);
            $label = $diff < 0
                ? 'Vencida'
                : ($diff === 0 ? 'Vence hoje' : ($diff === 1 ? 'Vence amanhã' : "Vence em {$diff} dias"));

            return [
                'type' => 'debt',
                'title' => $debt->name,
                'subtitle' => $debt->creditor,
                'days' => $diff,
                'label' => $label,
                'minimum' => (float) $debt->monthly_minimum,
                'status' => $diff <= 5 ? 'warn' : 'info',
                'link' => '/debts',
            ];
        })->values();

        $expenseValues = Transaction::query()
            ->forUser($userId)
            ->whereBetween('date', [$period['start_date'], $period['end_date']])
            ->where('amount', '<', 0)
            ->pluck('amount')
            ->map(fn ($value) => abs((float) $value));

        $expenseAverage = $expenseValues->avg() ?? 0;
        $suspicious = Transaction::query()
            ->forUser($userId)
            ->whereBetween('date', [$period['start_date'], $period['end_date']])
            ->where('amount', '<', 0)
            ->get()
            ->filter(function (Transaction $transaction) use ($expenseAverage) {
                $value = abs((float) $transaction->amount);
                return $expenseAverage > 0 && $value > max(1000, $expenseAverage * 2);
            })
            ->take(3)
            ->map(function (Transaction $transaction) {
                return [
                    'type' => 'suspicious',
                    'title' => 'Transação atípica',
                    'subtitle' => $transaction->description,
                    'days' => null,
                    'label' => 'Valor acima da média',
                    'minimum' => (float) abs($transaction->amount),
                    'status' => 'warn',
                    'link' => '/transactions',
                ];
            });

        $alerts = $alerts->merge($suspicious)->values();

        $planMonth = $period['month'] === 'all' ? null : (int) $period['month'];

        $plan = AiPlan::forUser($userId)
            ->where('year', $period['year'])
            ->where('month', $planMonth)
            ->orderByDesc('created_at')
            ->first();

        $summary = [
            'income' => (float) $income,
            'expenses' => (float) abs($expenses),
            'debt_minimum' => (float) $debtMinimum,
            'balance' => (float) $balance,
            'commitment_percent' => $commitmentPercent,
            'commitment_status' => $commitmentStatus,
        ];

        $previousPeriod = $period['month'] === 'all'
            ? PeriodContext::current($period['year'] - 1, 'all')
            : PeriodContext::current(
                Carbon::createFromDate($period['year'], (int) $period['month'], 1)->subMonth()->year,
                Carbon::createFromDate($period['year'], (int) $period['month'], 1)->subMonth()->format('m')
            );

        $prevIncome = Transaction::query()
            ->forUser($userId)
            ->whereBetween('date', [$previousPeriod['start_date'], $previousPeriod['end_date']])
            ->where('amount', '>', 0)
            ->sum('amount');

        $prevExpenses = Transaction::query()
            ->forUser($userId)
            ->whereBetween('date', [$previousPeriod['start_date'], $previousPeriod['end_date']])
            ->where('amount', '<', 0)
            ->sum('amount');

        $prevExpensesAbs = (float) abs($prevExpenses);

        $prevDebtMinimum = Debt::forUser($userId)
            ->whereIn('status', ['active', 'renegotiating'])
            ->sum('monthly_minimum');

        $comparison = [
            'period_label' => $period['month'] === 'all'
                ? 'vs ano anterior'
                : 'vs mês anterior',
            'income_diff' => (float) $income - (float) $prevIncome,
            'expenses_diff' => (float) abs($expenses) - (float) abs($prevExpenses),
            'debt_diff' => (float) $debtMinimum - (float) $prevDebtMinimum,
            'balance_diff' => (float) $balance - (float) ($prevIncome - abs($prevExpenses) - $prevDebtMinimum),
            'income_pct' => $prevIncome > 0 ? round((($income - $prevIncome) / $prevIncome) * 100, 1) : null,
            'expenses_pct' => $prevExpensesAbs > 0 ? round(((abs($expenses) - $prevExpensesAbs) / $prevExpensesAbs) * 100, 1) : null,
            'debt_pct' => $prevDebtMinimum > 0 ? round((($debtMinimum - $prevDebtMinimum) / $prevDebtMinimum) * 100, 1) : null,
            'balance_pct' => ($prevIncome - $prevExpensesAbs - $prevDebtMinimum) !== 0
                ? round((($balance - ($prevIncome - $prevExpensesAbs - $prevDebtMinimum)) / ($prevIncome - $prevExpensesAbs - $prevDebtMinimum)) * 100, 1)
                : null,
        ];

        $monthlyTrend = [];
        for ($month = 1; $month <= 12; $month += 1) {
            $start = Carbon::createFromDate($period['year'], $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $monthIncome = Transaction::query()
                ->forUser($userId)
                ->whereBetween('date', [$start, $end])
                ->where('amount', '>', 0)
                ->sum('amount');

            $monthExpenses = Transaction::query()
                ->forUser($userId)
                ->whereBetween('date', [$start, $end])
                ->where('amount', '<', 0)
                ->sum('amount');

            $monthlyTrend[] = [
                'month' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
                'income' => (float) $monthIncome,
                'expenses' => (float) abs($monthExpenses),
                'balance' => (float) $monthIncome - (float) abs($monthExpenses) - (float) $debtMinimum,
            ];
        }

        return Inertia::render('Pages/Home', [
            'summary' => $summary,
            'comparison' => $comparison,
            'debts' => $debts,
            'transactions' => $transactions,
            'alerts' => $alerts,
            'aiPlan' => $plan,
            'monthlyTrend' => $monthlyTrend,
            'period' => [
                'activeYear' => $period['year'],
                'activeMonth' => $period['month'],
            ],
        ]);
    }

    public function transactions()
    {
        return Inertia::render('Pages/Transactions');
    }

    public function debts()
    {
        return Inertia::render('Pages/Debts');
    }

    public function ai()
    {
        return Inertia::render('Pages/AI');
    }

    public function goals()
    {
        return Inertia::render('Pages/Goals');
    }

    public function reports()
    {
        return Inertia::render('Pages/Reports');
    }

    public function settings()
    {
        return Inertia::render('Pages/Settings');
    }
}
