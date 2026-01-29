<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiPlan extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'year',
        'month',
        'status',
        'summary_json',
    ];

    protected $casts = [
        'summary_json' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(AiPlanItem::class);
    }

    public function scopeForUser($query, $userId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        return $query->whereNull('user_id');
    }
}
