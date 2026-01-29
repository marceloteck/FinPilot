<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedView extends Model
{
    use HasFactory;
    protected $table = 'views';

    protected $fillable = [
        'user_id',
        'entity',
        'name',
        'is_default',
        'config_json',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'config_json' => 'array',
    ];

    public function scopeForUser($query, $userId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        return $query->whereNull('user_id');
    }
}
