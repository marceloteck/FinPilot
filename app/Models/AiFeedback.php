<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiFeedback extends Model
{
    use HasFactory;
    protected $table = 'ai_feedback';

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'action',
        'payload_json',
    ];

    protected $casts = [
        'payload_json' => 'array',
    ];

    public function scopeForUser($query, $userId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        return $query->whereNull('user_id');
    }
}
