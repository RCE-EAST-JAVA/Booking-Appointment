<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DateOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'is_available',
        'reason',
        'unavailable_slots',
        'unavailable_start',
        'unavailable_end',
        'unavailable_ranges',
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
        'unavailable_slots' => 'array',
        'unavailable_ranges' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
