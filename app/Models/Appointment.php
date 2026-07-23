<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'booking_code',
        'token',
        'student_name',
        'student_email',
        'nim',
        'department',
        'purpose',
        'notes',
        'file_path',
        'appointment_date',
        'time_slot',
        'status',
        'reschedule_reason',
        'proposed_date',
        'proposed_time_slot',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'proposed_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }
}
