<?php

namespace App\Http\Controllers\Finpilot;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finpilot\StoreDebtRequest;
use App\Http\Requests\Finpilot\UpdateDebtRequest;
use App\Models\Debt;
use App\Models\DebtType;
use App\Models\Transaction;
use App\Support\PeriodContext;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id;
        $activeYear = (int) $request->input('active_year');
        $activeMonth = $request->input('active_month', 'all');
        $period = PeriodContext::current($activeYear, $activeMonth);

        app()->instance('finpilot.period', $period);

        $debts = Debt::forUser($userId)
            ->with('type')
            ->whereIn('status', ['active', 'renegotiating'])
            ->orderBy('due_day')
            ->get();

        $incomeTotal = Transaction::query()
            ->forUser($userId)
            ->whereBetween('date', [$period['start_date'], $period['end_date']])
            ->where('amount', '>', 0)
            ->sum('amount');

        $debts->each(function (Debt $debt) use ($incomeTotal) {
            $debt->setAttribute('monthly_income', (float) $incomeTotal);
        });

        $totalMinimum = $debts->sum('monthly_minimum');
        $commitmentPercent = $incomeTotal > 0 ? round(($totalMinimum / $incomeTotal) * 100, 1) : null;
        $balanceAfter = $incomeTotal - $totalMinimum;

        $debtTypes = DebtType::forUser($userId)->orderBy('name')->get();
        $debtsByType = $debtTypes->map(function (DebtType $type) use ($debts) {
            return [
                'type' => $type,
                'debts' => $debts->where('debt_type_id', $type->id)->values(),
            ];
        })->values();

        return Inertia::render('Pages/Debts', [
            'debtsByType' => $debtsByType,
            'debtTypes' => $debtTypes,
            'summary' => [
                'income' => (float) $incomeTotal,
                'total_minimum' => (float) $totalMinimum,
                'commitment_percent' => $commitmentPercent,
                'balance_after' => (float) $balanceAfter,
            ],
            'period' => [
                'activeYear' => $period['year'],
                'activeMonth' => $period['month'],
            ],
        ]);
    }

    public function store(StoreDebtRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()?->id;
        Debt::create($data);

        return redirect()->back()->with('success', 'Dívida criada com sucesso.');
    }

    public function update(UpdateDebtRequest $request, Debt $debt)
    {
        $debt->update($request->validated());

        return redirect()->back()->with('success', 'Dívida atualizada.');
    }

    public function destroy(Debt $debt)
    {
        $debt->delete();

        return redirect()->back()->with('success', 'Dívida removida.');
    }
}
