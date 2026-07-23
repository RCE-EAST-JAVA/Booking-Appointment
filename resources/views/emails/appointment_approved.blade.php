<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Janji Bimbingan Disetujui</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #059669; color: #ffffff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 600; }
        .content { padding: 30px; }
        .badge-success { display: inline-block; background: #d1fae5; color: #065f46; font-weight: bold; padding: 6px 12px; border-radius: 4px; font-size: 14px; margin-bottom: 16px; }
        .table-details { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table-details td { padding: 10px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        .table-details td.label { font-weight: 600; color: #4b5563; width: 35%; }
        .footer { background: #f9fafb; padding: 16px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Portal Bimbingan Akademik</h1>
        </div>
        <div class="content">
            <div class="badge-success">✓ APPOINTMENT APPROVED</div>
            <h2>Selamat, {{ $appointment->student_name }}!</h2>
            <p>Pengajuan janji bimbingan Anda dengan Kode Booking <strong>{{ $appointment->booking_code }}</strong> telah <strong>DISETUJUI</strong> oleh Dosen Pembimbing.</p>

            <h3>Jadwal Pertemuan:</h3>
            <table class="table-details">
                <tr>
                    <td class="label">Tanggal</td>
                    <td><strong>{{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('l, d F Y') }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Jam / Slot</td>
                    <td><strong>{{ $appointment->time_slot }} WIB</strong></td>
                </tr>
                <tr>
                    <td class="label">Keperluan</td>
                    <td>{{ $appointment->purpose }}</td>
                </tr>
                <tr>
                    <td class="label">Dosen Pembimbing</td>
                    <td>{{ $appointment->user?->name ?? 'Dosen Pembimbing' }}</td>
                </tr>
            </table>

            <p style="color: #4b5563; font-size: 14px;">Mohon hadir tepat waktu sesuai jadwal yang disepakati. Terima kasih.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Portal Bimbingan Akademik. All rights reserved.
        </div>
    </div>
</body>
</html>
