<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiPlanItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'ai_plan_id',
        'debt_id',
        'bucket',
        'suggested_amount',
        'confirmed_amount',
        'reason_json',
    ];

    protected $casts = [
        'suggested_amount' => 'decimal:2',
        'confirmed_amount' => 'decimal:2',
        'reason_json' => 'array',
    ];

    public function scopeForUser($query, $userId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        return $query->whereNull('user_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(AiPlan::class, 'ai_plan_id');
    }

    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }
}
