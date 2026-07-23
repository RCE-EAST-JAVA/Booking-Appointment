# 📅 Sistem Informasi Manajemen Bimbingan & Janji Temu Akademik (Booking Appointment System)

Sistem Informasi Manajemen Bimbingan & Janji Temu Akademik adalah aplikasi berbasis web modern yang dirancang untuk mempermudah proses penjadwalan bimbingan skripsi, konseling akademik, dan janji temu antara Mahasiswa dan Dosen.

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![AlpineJS](https://img.shields.io/badge/Alpine.js-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=black)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)

---

## ✨ Fitur Utama

### 👨‍🎓 **Portal Mahasiswa**
- 📝 **Formulir Janji Bimbingan**: Pendaftaran janji temu instan dengan validasi nama, NIM, prodi, keperluan, dan catatan bimbingan.
- ⏱️ **Slot Waktu & Kuota Real-Time**: Slot jam dan sisa kuota bimbingan dihitung secara otomatis. Slot jam yang beririsan dengan rapat/dinas dosen otomatis diblokir.
- 🔍 **Tracking Status Janji**: Pengecekan status janji bimbingan menggunakan Kode Booking unik (contoh: `BMB-20260723-XXXX`).
- 🔄 **Aksi Reschedule Mahasiswa**: Mahasiswa dapat menyetujui atau menolak usulan jadwal ulang dari Dosen secara interaktif.

### 👨‍🏫 **Dashboard Dosen & Admin**
- 🗓️ **Kalender Visual Interaktif**: Tampilan kalender bulanan dengan badge jumlah mahasiswa terdaftar pada tiap tile tanggal dan modal detail pendaftar.
- ⏰ **Pengaturan Jam Kerja & Multi-Range Jam Off**: Penentuan slot bimbingan mingguan serta pemblokiran jam tertentu (misal: Rapat jam 08:30-09:15 dan Dinas jam 13:00-14:00).
- 🇮🇩 **Integrasi Open API Tanggal Merah Indonesia**: Sinkronisasi otomatis daftar Libur Nasional & Cuti Bersama Indonesia langsung dari Open API (`APIHariLibur_V2`).
- ⚡ **Quick Action AJAX Tanpa Refresh**: Persetujuan (*Setujui*), penolakan (*Tolak*), penyesuaian jadwal (*Reschedule*), dan penandaan *Selesai* dieksekusi secara instan dengan indikator loading spinner.
- 📢 **Pengumuman Publik**: Fitur pengumuman bergerak/banner penting yang dapat diaktifkan atau dinonaktifkan oleh Admin untuk tampil pada halaman depan mahasiswa.
- 🔑 **Fitur 'Ingat Saya' (Remember Me)**: Login dosen persisten dengan token enkripsi agar tidak perlu login berulang.
- 📱 **Fully Mobile Responsive**: Desain antarmuka fleksibel dengan menu navigasi hamburger pada perangkat ponsel/HP.

---

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 8.2+, Laravel 11 Framework
- **Frontend**: Blade Templating, Tailwind CSS, Alpine.js, Lucide Icons
- **Database**: SQLite / MySQL
- **Integrasi API**: Open API Hari Libur Nasional Indonesia (`guangrei/APIHariLibur_V2`)

---

## 🚀 Panduan Instalasi & Jalankan Aplikasi (How to Setup and Run)

Prasyarat sebelum menginstal:
- PHP >= 8.2
- Composer
- Node.js & NPM
- XAMPP / web server lokal (opsional)

### 1. Clone Repository
```bash
git clone https://github.com/RCE-EAST-JAVA/Booking-Appointment.git
cd Booking-Appointment
```

### 2. Instal Dependensi Composer & NPM
```bash
composer install
npm install
```

### 3. Salin Environment File & Generate Key
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database & Jalankan Migration + Seeder
Pastikan file database SQLite (`database/database.sqlite`) telah dibuat atau disesuaikan pada `.env`, lalu jalankan command berikut untuk membuat tabel dan data dummy:
```bash
php artisan migrate:fresh --seed
```

### 5. Jalankan Local Development Server
```bash
php artisan serve
```
Aplikasi dapat diakses di browser melalui link: **`http://127.0.0.1:8000`**

---

## 🔐 Kredensial Login Admin Default

Gunakan kredensial berikut untuk masuk ke Portal Dosen/Admin:

| Parameter | Value |
| --- | --- |
| **URL Login Admin** | `http://127.0.0.1:8000/admin/login` |
| **Username** | `honest` |
| **Password** | `honest2026` |

---

## 📄 Lisensi
Hak Cipta &copy; {{ date('Y') }} Sistem Informasi Manajemen Bimbingan & Janji Temu Akademik. Dikembangkan di bawah lisensi MIT.
