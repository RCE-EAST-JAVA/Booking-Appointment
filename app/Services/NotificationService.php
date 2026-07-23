<?php

namespace App\Services;

use App\Models\EmailLog;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotificationService
{
    public static function send(string $recipient, Mailable $mailable, ?int $appointmentId = null): bool
    {
        SmtpConfigService::applyConfig();

        $subject = $mailable->subject ?? 'Notifikasi Bimbingan Akademik';

        try {
            Mail::to($recipient)->send($mailable);

            EmailLog::create([
                'appointment_id' => $appointmentId,
                'recipient' => $recipient,
                'subject' => $subject,
                'status' => 'sent',
                'error_message' => null,
            ]);

            return true;
        } catch (Throwable $e) {
            EmailLog::create([
                'appointment_id' => $appointmentId,
                'recipient' => $recipient,
                'subject' => $subject,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
