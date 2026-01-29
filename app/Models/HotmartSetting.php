<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotmartSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_enabled',
        'product_id',
        'basic_key',
        'basic_secret',
        'webhook_secret',
        'webhook_url',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'basic_key' => 'encrypted',
        'basic_secret' => 'encrypted',
        'webhook_secret' => 'encrypted',
    ];

    public function scopeForUser($query, $userId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        return $query->whereNull('user_id');
    }
}
