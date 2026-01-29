<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DebtType extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
    ];

    public function scopeForUser($query, $userId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        return $query->whereNull('user_id');
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }
}
