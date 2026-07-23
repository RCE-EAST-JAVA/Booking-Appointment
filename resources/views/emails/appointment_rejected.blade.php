<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Janji Bimbingan Ditolak</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #dc2626; color: #ffffff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 600; }
        .content { padding: 30px; }
        .reason-box { background: #fef2f2; border-left: 4px solid #ef4444; padding: 14px; margin: 16px 0; border-radius: 4px; font-size: 14px; color: #991b1b; }
        .footer { background: #f9fafb; padding: 16px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Portal Bimbingan Akademik</h1>
        </div>
        <div class="content">
            <h2>Halo, {{ $appointment->student_name }}</h2>
            <p>Mohon maaf, pengajuan janji bimbingan Anda (Kode Booking: <strong>{{ $appointment->booking_code }}</strong>) untuk tanggal <strong>{{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d F Y') }}</strong> jam <strong>{{ $appointment->time_slot }} WIB</strong> belum dapat disetujui.</p>

            <div class="reason-box">
                <strong>Alasan Penolakan:</strong><br>
                <em>"{{ $appointment->reschedule_reason ?? 'Dosen berhalangan pada waktu tersebut.' }}"</em>
            </div>

            <p style="font-size: 14px; color: #4b5563;">Anda dapat memilih waktu/slot lain dengan mendaftarkan kembali janji bimbingan di portal utama.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Portal Bimbingan Akademik. All rights reserved.
        </div>
    </div>
</body>
</html>
