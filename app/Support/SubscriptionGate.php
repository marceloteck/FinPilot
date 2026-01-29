<?php

namespace App\Support;

use App\Models\SubscriptionSetting;

class SubscriptionGate
{
    public static function settings(?int $userId = null): SubscriptionSetting
    {
        return SubscriptionSetting::forUser($userId)->firstOrCreate(['user_id' => $userId], [
            'ai_enabled' => true,
            'import_enabled' => true,
            'reports_enabled' => true,
            'max_users' => null,
        ]);
    }

    public static function aiEnabled(?int $userId = null): bool
    {
        return self::settings($userId)->ai_enabled;
    }

    public static function importEnabled(?int $userId = null): bool
    {
        return self::settings($userId)->import_enabled;
    }

    public static function reportsEnabled(?int $userId = null): bool
    {
        return self::settings($userId)->reports_enabled;
    }
}
