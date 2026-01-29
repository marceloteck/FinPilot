<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiPreference extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'payoff_strategy',
        'risk_tolerance',
        'weights_json',
    ];

    protected $casts = [
        'weights_json' => 'array',
    ];

    public function scopeForUser($query, $userId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        return $query->whereNull('user_id');
    }
}
