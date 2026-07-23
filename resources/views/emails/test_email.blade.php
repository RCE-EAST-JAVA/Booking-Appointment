<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Uji Coba SMTP - Portal Bimbingan</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px; color: #333;">
    <div style="max-width: 500px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; background: #fff;">
        <h2 style="color: #2563eb; margin-top: 0;">✓ Uji Coba SMTP Berhasil!</h2>
        <p>Email ini adalah email uji coba otomatis yang dikirim dari <strong>Portal Bimbingan Akademik</strong> untuk memverifikasi bahwa pengaturan SMTP server Anda telah dikonfigurasi dengan benar.</p>
        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
        <p style="font-size: 12px; color: #64748b; margin: 0;">Tanggal Tes: {{ now()->translatedFormat('l, d F Y H:i:s') }} WIB</p>
    </div>
</body>
</html>
