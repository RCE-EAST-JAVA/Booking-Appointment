<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        $admin = User::firstOrCreate(
            ['email' => 'honest'],
            [
                'name' => 'Honest Dody Molasy',
                'password' => Hash::make('honest2026'),
                'role' => 'admin',
            ]
        );

        // 2. Default Schedules (Monday to Friday: Day 1 to 5)
        $timeSlots = ['09:00 - 10:00', '10:00 - 11:00', '13:00 - 14:00', '14:00 - 15:00'];

        for ($day = 1; $day <= 5; $day++) {
            foreach ($timeSlots as $slot) {
                Schedule::firstOrCreate(
                    [
                        'user_id' => $admin->id,
                        'day_of_week' => $day,
                        'time_slot' => $slot,
                    ],
                    [
                        'quota' => 3,
                        'is_active' => true,
                    ]
                );
            }
        }

        // 3. Default SMTP Configuration
        SmtpSetting::firstOrCreate(
            ['id' => 1],
            [
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'admin@portal.ac.id',
                'password' => 'secret_app_password',
                'encryption' => 'tls',
                'from_email' => 'no-reply@portal-bimbingan.ac.id',
                'from_name' => 'Portal Bimbingan Akademik',
                'is_active' => true,
            ]
        );
    }
}
