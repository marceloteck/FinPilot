<?php

namespace App\Http\Controllers\Finpilot;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finpilot\HotmartSettingsRequest;
use App\Models\HotmartSetting;
use App\Models\SubscriptionSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AdminHotmartController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id;
        $setting = HotmartSetting::forUser($userId)->first();
        $subscription = SubscriptionSetting::forUser($userId)->first();

        return Inertia::render('Pages/Admin/Hotmart', [
            'setting' => $setting,
            'subscription' => $subscription,
        ]);
    }

    public function update(HotmartSettingsRequest $request)
    {
        $userId = $request->user()?->id;
        $data = $request->validated();
        $data['user_id'] = $userId;
        $data['is_enabled'] = (bool) ($data['is_enabled'] ?? false);

        $subscription = [
            'ai_enabled' => $data['ai_enabled'] ?? true,
            'import_enabled' => $data['import_enabled'] ?? true,
            'reports_enabled' => $data['reports_enabled'] ?? true,
            'max_users' => $data['max_users'] ?? null,
            'user_id' => $userId,
        ];

        HotmartSetting::updateOrCreate(
            ['user_id' => $userId],
            $data
        );

        SubscriptionSetting::updateOrCreate(
            ['user_id' => $userId],
            $subscription
        );

        Log::info('Hotmart settings updated', [
            'user_id' => $userId,
            'hotmart_enabled' => $data['is_enabled'],
        ]);

        return redirect()->back()->with('success', 'Configurações da Hotmart atualizadas.');
    }
}
