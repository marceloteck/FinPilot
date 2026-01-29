<?php

namespace App\Http\Controllers\Finpilot;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finpilot\AiFeedbackRequest;
use App\Http\Requests\Finpilot\ConfirmAiPlanRequest;
use App\Models\AiFeedback;
use App\Models\AiPlan;
use App\Models\AiPlanItem;
use App\Models\AiPreference;
use App\Services\AI\PaymentPlanService;
use App\Support\PeriodContext;
use App\Support\SubscriptionGate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id;
        $activeYear = (int) $request->input('active_year');
        $activeMonth = $request->input('active_month', 'all');
        $period = PeriodContext::current($activeYear, $activeMonth);

        if (!SubscriptionGate::aiEnabled($userId)) {
            return redirect()->back()->with('warning', 'Seu plano não permite o uso do Assistente IA.');
        }

        $preferences = AiPreference::forUser($userId)->firstOrCreate(['user_id' => $userId], [
            'payoff_strategy' => 'avalanche',
            'risk_tolerance' => 'normal',
        ]);

        $plan = AiPlan::forUser($userId)
            ->with('items.debt.type')
            ->where('year', $period['year'])
            ->where('month', $period['month'] === 'all' ? null : (int) $period['month'])
            ->orderByDesc('created_at')
            ->first();

        return Inertia::render('Pages/AI', [
            'preferences' => $preferences,
            'plan' => $plan,
            'period' => [
                'activeYear' => $period['year'],
                'activeMonth' => $period['month'],
            ],
        ]);
    }

    public function generate(Request $request, PaymentPlanService $service)
    {
        $userId = $request->user()?->id;
        $activeYear = (int) $request->input('active_year');
        $activeMonth = $request->input('active_month', 'all');
        $period = PeriodContext::current($activeYear, $activeMonth);

        if (!SubscriptionGate::aiEnabled($userId)) {
            return redirect()->back()->with('warning', 'Seu plano não permite o uso do Assistente IA.');
        }

        $preferences = AiPreference::forUser($userId)->firstOrCreate(['user_id' => $userId], [
            'payoff_strategy' => 'avalanche',
            'risk_tolerance' => 'normal',
        ]);

        $result = $service->generate($period['year'], $period['month'], $preferences->payoff_strategy, $userId);

        AiPlan::forUser($userId)
            ->where('year', $period['year'])
            ->where('month', $period['month'] === 'all' ? null : (int) $period['month'])
            ->where('status', 'draft')
            ->delete();

        $plan = AiPlan::create([
            'user_id' => $userId,
            'year' => $period['year'],
            'month' => $period['month'] === 'all' ? null : (int) $period['month'],
            'status' => 'draft',
            'summary_json' => $result['summary'],
        ]);

        foreach ($result['mandatory'] as $item) {
            AiPlanItem::create([
                'user_id' => $userId,
                'ai_plan_id' => $plan->id,
                'debt_id' => $item['debt']->id,
                'bucket' => 'mandatory',
                'suggested_amount' => $item['suggested_amount'],
                'reason_json' => $item['reason_json'],
            ]);
        }

        foreach ($result['priority'] as $item) {
            AiPlanItem::create([
                'user_id' => $userId,
                'ai_plan_id' => $plan->id,
                'debt_id' => $item['debt']->id,
                'bucket' => 'priority',
                'suggested_amount' => $item['suggested_amount'],
                'reason_json' => $item['reason_json'],
            ]);
        }

        if ($result['overflow'] > 0) {
            AiPlanItem::create([
                'user_id' => $userId,
                'ai_plan_id' => $plan->id,
                'bucket' => 'overflow',
                'suggested_amount' => $result['overflow'],
                'reason_json' => ['reasons' => ['Reserva de emergência'], 'score' => 0],
            ]);
        }

        Log::info('AI plan generated', [
            'user_id' => $userId,
            'year' => $period['year'],
            'month' => $period['month'],
            'plan_id' => $plan->id,
        ]);

        return redirect()->back()->with('success', 'Plano gerado com sucesso.');
    }

    public function confirm(ConfirmAiPlanRequest $request)
    {
        $data = $request->validated();
        $userId = $request->user()?->id;

        if (!SubscriptionGate::aiEnabled($userId)) {
            return redirect()->back()->with('warning', 'Seu plano não permite o uso do Assistente IA.');
        }

        $plan = AiPlan::forUser($userId)->with('items')->findOrFail($data['ai_plan_id']);

        foreach ($data['items'] as $item) {
            AiPlanItem::forUser($userId)
                ->where('id', $item['id'])
                ->where('ai_plan_id', $plan->id)
                ->update(['confirmed_amount' => $item['confirmed_amount']]);
        }

        $plan->update(['status' => 'confirmed']);

        AiFeedback::create([
            'user_id' => $userId,
            'year' => $plan->year,
            'month' => $plan->month,
            'action' => 'confirm_plan',
            'payload_json' => ['ai_plan_id' => $plan->id],
        ]);

        Log::info('AI plan confirmed', [
            'user_id' => $userId,
            'plan_id' => $plan->id,
        ]);

        return redirect()->back()->with('success', 'Plano confirmado.');
    }

    public function feedback(AiFeedbackRequest $request)
    {
        $data = $request->validated();
        $payload = $data['payload'] ?? [];
        $userId = $request->user()?->id;

        if ($data['action'] === 'change_strategy' && isset($payload['strategy'])) {
            AiPreference::forUser($userId)->firstOrCreate(['user_id' => $userId], [
                'payoff_strategy' => 'avalanche',
                'risk_tolerance' => 'normal',
            ])->update(['payoff_strategy' => $payload['strategy']]);
        }

        AiFeedback::create([
            'user_id' => $userId,
            'action' => $data['action'],
            'payload_json' => $payload,
            'year' => $payload['year'] ?? now()->year,
            'month' => $payload['month'] ?? null,
        ]);

        Log::info('AI feedback recorded', [
            'user_id' => $userId,
            'action' => $data['action'],
        ]);

        return redirect()->back()->with('success', 'Feedback registrado.');
    }
}
