<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ai_enabled',
        'import_enabled',
        'reports_enabled',
        'max_users',
    ];

    protected $casts = [
        'ai_enabled' => 'boolean',
        'import_enabled' => 'boolean',
        'reports_enabled' => 'boolean',
    ];

    public function scopeForUser($query, $userId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        return $query->whereNull('user_id');
    }
}
