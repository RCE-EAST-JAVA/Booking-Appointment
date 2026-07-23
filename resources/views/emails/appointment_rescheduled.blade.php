<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Perubahan Jadwal Bimbingan</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #d97706; color: #ffffff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 600; }
        .content { padding: 30px; }
        .reason-box { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px; margin: 16px 0; border-radius: 4px; font-size: 14px; }
        .compare-box { display: flex; justify-content: space-between; margin: 20px 0; gap: 10px; }
        .schedule-card { background: #f3f4f6; border-radius: 6px; padding: 12px; width: 48%; box-sizing: border-box; }
        .schedule-card.new { background: #ecfdf5; border: 1px solid #a7f3d0; }
        .schedule-card h4 { margin: 0 0 8px 0; font-size: 12px; text-transform: uppercase; color: #6b7280; }
        .schedule-card.new h4 { color: #047857; }
        .action-btn { display: inline-block; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 14px; margin: 6px 4px; text-align: center; }
        .btn-accept { background: #059669; color: #ffffff; }
        .btn-cancel { background: #ef4444; color: #ffffff; }
        .footer { background: #f9fafb; padding: 16px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Perubahan Jadwal Bimbingan</h1>
        </div>
        <div class="content">
            <h2>Halo, {{ $appointment->student_name }}</h2>
            <p>Dosen Pembimbing telah mengajukan <strong>perubahan jadwal (reschedule)</strong> untuk janji bimbingan Anda (Kode Booking: <strong>{{ $appointment->booking_code }}</strong>).</p>

            <div class="reason-box">
                <strong>Alasan Perubahan:</strong><br>
                <em>"{{ $appointment->reschedule_reason ?? 'Penyesuaian agenda dosen' }}"</em>
            </div>

            <h3>Perbandingan Jadwal:</h3>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
                <tr>
                    <td width="48%" style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; vertical-align: top;">
                        <span style="font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: bold;">JADWAL SEMULA</span>
                        <div style="font-size: 14px; margin-top: 6px; color: #9ca3af; text-decoration: line-through;">
                            {{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d M Y') }}<br>
                            {{ $appointment->time_slot }} WIB
                        </div>
                    </td>
                    <td width="4%"></td>
                    <td width="48%" style="background: #ecfdf5; border: 1px solid #6ee7b7; border-radius: 6px; padding: 12px; vertical-align: top;">
                        <span style="font-size: 11px; text-transform: uppercase; color: #047857; font-weight: bold;">JADWAL BARU (PROPOSAL)</span>
                        <div style="font-size: 14px; margin-top: 6px; color: #065f46; font-weight: bold;">
                            {{ \Carbon\Carbon::parse($appointment->proposed_date)->translatedFormat('d M Y') }}<br>
                            {{ $appointment->proposed_time_slot }} WIB
                        </div>
                    </td>
                </tr>
            </table>

            <p style="font-size: 14px; margin-top: 24px;">Silakan konfirmasi pilihan Anda dengan mengklik salah satu tombol di bawah ini:</p>

            <div style="text-align: center; margin: 24px 0;">
                <a href="{{ route('student.reschedule.action', ['token' => $appointment->token, 'action' => 'accept']) }}" class="action-btn btn-accept">✓ Terima Jadwal Baru</a>
                <a href="{{ route('student.reschedule.action', ['token' => $appointment->token, 'action' => 'cancel']) }}" class="action-btn btn-cancel">✕ Batalkan Pengajuan</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Portal Bimbingan Akademik. All rights reserved.
        </div>
    </div>
</body>
</html>
