<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dosen = User::where('role', 'admin')->first() ?? User::first();
        if (!$dosen) return;

        $studentNames = [
            'Budi Santoso', 'Siti Rahmawati', 'Ahmad Fauzi', 'Dewi Lestari',
            'Rizky Pratama', 'Nabila Putri', 'Fikri Haikal', 'Andi Wijaya',
            'Rina Kurnia', 'Bayu Putra', 'Anisa Fitri', 'Dian Sastrowardoyo',
            'Eko Prasetyo', 'Gita Gutawa', 'Hendra Setiawan', 'Indah Permata',
            'Joko Widodo', 'Kartika Putri', 'Lukman Hakim', 'Maya Estianty',
            'Nugroho Djati', 'Oky Setiana', 'Panji Petualang', 'Qory Sandioriva'
        ];

        $departments = [
            'Teknik Informatika',
            'Sistem Informasi',
            'Teknik Komputer',
            'Data Science',
            'Rekayasa Perangkat Lunak'
        ];

        $purposes = ['Bimbingan', 'Tanda Tangan', 'Lain-lain'];
        $statuses = ['approved', 'pending', 'completed', 'rescheduled', 'rejected'];

        $timeSlots = ['08:00 - 09:00', '09:00 - 10:00', '10:00 - 11:00', '13:00 - 14:00', '14:00 - 15:00'];

        $startDate = Carbon::create(2026, 7, 1);
        $endDate = Carbon::create(2026, 8, 31);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Skip weekends mostly
            if ($date->isWeekend() && rand(0, 10) > 2) {
                continue;
            }

            // Create 1-3 appointments per day
            $numAppointments = rand(1, 3);
            for ($i = 0; $i < $numAppointments; $i++) {
                $name = $studentNames[array_rand($studentNames)];
                $slot = $timeSlots[array_rand($timeSlots)];
                $status = $statuses[array_rand($statuses)];
                $purpose = $purposes[array_rand($purposes)];
                $dept = $departments[array_rand($departments)];
                
                $nim = '2204' . sprintf('%06d', rand(100, 99999));
                $email = Str::slug($name, '.') . '@student.ac.id';
                $bookingCode = 'BMB-' . $date->format('Ymd') . '-' . strtoupper(Str::random(4));
                $token = Str::random(32);

                Appointment::create([
                    'user_id' => $dosen->id,
                    'booking_code' => $bookingCode,
                    'token' => $token,
                    'student_name' => $name,
                    'student_email' => $email,
                    'nim' => $nim,
                    'department' => $dept,
                    'purpose' => $purpose,
                    'notes' => 'Diskusi progres bab ' . rand(1, 5) . ' dan review laporan.',
                    'appointment_date' => $date->format('Y-m-d'),
                    'time_slot' => $slot,
                    'status' => $status,
                    'reschedule_reason' => in_array($status, ['rejected', 'rescheduled']) ? 'Penyesuaian agenda dosen' : null,
                    'proposed_date' => $status === 'rescheduled' ? $date->copy()->addDays(2)->format('Y-m-d') : null,
                    'proposed_time_slot' => $status === 'rescheduled' ? '10:00 - 11:00' : null,
                ]);
            }
        }
    }
}
