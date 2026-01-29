<?php

namespace App\Services\AI;

use App\Models\Debt;
use App\Models\Transaction;
use App\Support\PeriodContext;
use Carbon\Carbon;

class PaymentPlanService
{
    public function generate(int $year, ?string $month, string $strategy = 'avalanche', ?int $userId = null): array
    {
        $period = PeriodContext::current($year, $month ?? 'all');

        app()->instance('finpilot.period', $period);

        $incomeMonth = Transaction::query()
            ->forUser($userId)
            ->whereBetween('date', [$period['start_date'], $period['end_date']])
            ->where('amount', '>', 0)
            ->sum('amount');

        $expensesMonth = Transaction::query()
            ->forUser($userId)
            ->whereBetween('date', [$period['start_date'], $period['end_date']])
            ->where('amount', '<', 0)
            ->sum('amount');

        $expensesMonth = abs($expensesMonth);

        $debts = Debt::forUser($userId)
            ->with('type')
            ->whereIn('status', ['active', 'renegotiating'])
            ->get();

        $debts->each(function (Debt $debt) use ($incomeMonth) {
            $debt->setAttribute('monthly_income', (float) $incomeMonth);
        });

        $debtMinimumTotal = $debts->sum('monthly_minimum');
        $available = max(0, $incomeMonth - $debtMinimumTotal);

        $mandatoryItems = $debts->map(function (Debt $debt) {
            return [
                'debt' => $debt,
                'bucket' => 'mandatory',
                'suggested_amount' => (float) $debt->monthly_minimum,
                'reason_json' => $this->buildReasons($debt),
            ];
        })->values();

        $priorityItems = [];
        $extra = $available;

        if ($extra > 0 && $debts->count() > 0) {
            $ordered = $strategy === 'snowball'
                ? $debts->sortBy(fn (Debt $debt) => $debt->total_amount ?? 0)->values()
                : $debts->sortByDesc(fn (Debt $debt) => $debt->priority_score)->values();

            $totalScore = $ordered->sum(fn (Debt $debt) => max(1, $debt->priority_score));

            foreach ($ordered as $debt) {
                $weight = max(1, $debt->priority_score) / max(1, $totalScore);
                $suggested = round($extra * $weight, 2);

                if ($suggested <= 0) {
                    continue;
                }

                $priorityItems[] = [
                    'debt' => $debt,
                    'bucket' => 'priority',
                    'suggested_amount' => $suggested,
                    'reason_json' => $this->buildReasons($debt),
                ];
            }
        }

        $allocatedExtra = collect($priorityItems)->sum('suggested_amount');
        $overflow = max(0, ($incomeMonth - $debtMinimumTotal) - $allocatedExtra);

        $summary = [
            'income' => (float) $incomeMonth,
            'expenses' => (float) $expensesMonth,
            'minimum' => (float) $debtMinimumTotal,
            'available' => (float) ($incomeMonth - $debtMinimumTotal),
            'overflow' => (float) $overflow,
            'strategy' => $strategy,
        ];

        return [
            'period' => $period,
            'summary' => $summary,
            'mandatory' => $mandatoryItems,
            'priority' => $priorityItems,
            'overflow' => $overflow,
        ];
    }

    protected function buildReasons(Debt $debt): array
    {
        $reasons = [];
        $today = Carbon::now();
        $dueDate = $today->copy()->day($debt->due_day);
        $diff = $today->diffInDays($dueDate, false);

        if ($diff >= 0 && $diff <= 5) {
            $reasons[] = 'Vence em até 5 dias';
        }

        if ($debt->type && str_contains(strtolower($debt->type->name), 'cart')) {
            $reasons[] = 'Tipo: Cartão (alto risco)';
        }

        $income = (float) ($debt->monthly_income ?? 0);
        if ($income > 0 && $debt->monthly_minimum > ($income * 0.2)) {
            $reasons[] = 'Parcela pesa na renda do mês';
        }

        if ($debt->status === 'renegotiating') {
            $reasons[] = 'Em renegociação';
        }

        if (count($reasons) < 2) {
            $reasons[] = 'Prioridade calculada pelo score';
        }

        return [
            'reasons' => array_slice($reasons, 0, 4),
            'score' => $debt->priority_score,
        ];
    }
}
