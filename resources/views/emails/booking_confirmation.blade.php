<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Konfirmasi Pendaftaran Bimbingan</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #1e3a8a; color: #ffffff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 600; }
        .content { padding: 30px; }
        .code-box { background: #eff6ff; border: 2px dashed #3b82f6; border-radius: 6px; padding: 16px; text-align: center; margin: 20px 0; }
        .code-box span { font-size: 24px; font-weight: bold; color: #1e40af; letter-spacing: 2px; }
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
            <h2>Halo, {{ $appointment->student_name }}</h2>
            <p>Pengajuan janji bimbingan Anda telah kami terima dengan status <strong>PENDING</strong>.</p>
            
            <div class="code-box">
                <div style="font-size: 12px; color: #3b82f6; margin-bottom: 4px;">KODE BOOKING ANDA</div>
                <span>{{ $appointment->booking_code }}</span>
            </div>

            <h3>Rincian Pengajuan:</h3>
            <table class="table-details">
                <tr>
                    <td class="label">NIM</td>
                    <td>{{ $appointment->nim }}</td>
                </tr>
                <tr>
                    <td class="label">Jurusan</td>
                    <td>{{ $appointment->department }}</td>
                </tr>
                <tr>
                    <td class="label">Keperluan</td>
                    <td>{{ $appointment->purpose }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Bimbingan</td>
                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('l, d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Waktu / Slot</td>
                    <td>{{ $appointment->time_slot }} WIB</td>
                </tr>
            </table>

            <p>Gunakan Kode Booking atau NIM Anda untuk memantau status persetujuan pada Portal Bimbingan.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Portal Bimbingan Akademik. All rights reserved.
        </div>
    </div>
</body>
</html>
