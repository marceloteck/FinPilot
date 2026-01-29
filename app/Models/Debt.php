<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Debt extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'debt_type_id',
        'name',
        'creditor',
        'total_amount',
        'monthly_minimum',
        'interest_rate',
        'due_day',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'monthly_minimum' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $appends = [
        'monthly_impact',
        'is_due_this_month',
        'priority_score',
    ];

    public function scopeForUser($query, $userId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        return $query->whereNull('user_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DebtType::class, 'debt_type_id');
    }

    public function getMonthlyImpactAttribute(): float
    {
        return (float) $this->monthly_minimum;
    }

    public function getIsDueThisMonthAttribute(): bool
    {
        $period = app()->bound('finpilot.period') ? app('finpilot.period') : null;
        $activeMonth = $period['month'] ?? Carbon::now()->format('m');
        $activeYear = $period['year'] ?? Carbon::now()->year;

        if ($activeMonth === 'all') {
            return true;
        }

        $monthValue = str_pad((string) $activeMonth, 2, '0', STR_PAD_LEFT);
        $daysInMonth = Carbon::createFromDate($activeYear, (int) $monthValue, 1)->daysInMonth;
        $day = min((int) $this->due_day, $daysInMonth);

        return Carbon::createFromDate($activeYear, (int) $monthValue, $day)->format('m') === $monthValue;
    }

    public function getPriorityScoreAttribute(): int
    {
        $base = 50;
        $score = $base;

        $today = Carbon::now();
        $dueDate = $today->copy()->day($this->due_day);
        $diff = $today->diffInDays($dueDate, false);
        if ($diff >= 0 && $diff <= 5) {
            $score += 30;
        }

        if ($this->type && str_contains(strtolower($this->type->name), 'cart')) {
            $score += 20;
        }

        $income = (float) ($this->monthly_income ?? 0);
        if ($income > 0 && $this->monthly_minimum > ($income * 0.2)) {
            $score += 15;
        }

        if ($this->monthly_minimum > 800) {
            $score += 10;
        }

        if ($this->status === 'renegotiating') {
            $score += 10;
        }

        return min(100, $score);
    }
}
