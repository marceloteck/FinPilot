<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'color',
        'is_hidden',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function scopeForUser($query, $userId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        return $query->whereNull('user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
