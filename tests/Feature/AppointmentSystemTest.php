<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin Lecturer
        $this->user = User::create([
            'name' => 'Dr. Dosen',
            'email' => 'admin@portal.ac.id',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // Create Monday Schedule
        Schedule::create([
            'user_id' => $this->user->id,
            'day_of_week' => 1, // Monday
            'time_slot' => '09:00 - 10:00',
            'quota' => 2,
            'is_active' => true,
        ]);

        // Create SMTP Setting
        SmtpSetting::create([
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => 'admin@portal.ac.id',
            'password' => 'secret',
            'encryption' => 'tls',
            'from_email' => 'no-reply@portal.ac.id',
            'from_name' => 'Portal Bimbingan',
            'is_active' => true,
        ]);
    }

    public function test_student_can_view_registration_page()
    {
        $response = $this->get(route('student.index'));
        $response->assertStatus(200);
        $response->assertSee('Formulir Janji Bimbingan Akademik');
    }

    public function test_student_can_check_available_slots()
    {
        // Next Monday date
        $monday = date('Y-m-d', strtotime('next Monday'));

        $response = $this->getJson(route('student.available-slots', ['date' => $monday]));
        $response->assertStatus(200);
        $response->assertJson([
            'is_blocked' => false,
            'slots' => [
                [
                    'time_slot' => '09:00 - 10:00',
                    'quota' => 2,
                    'booked' => 0,
                    'remaining' => 2,
                    'is_available' => true,
                ]
            ]
        ]);
    }

    public function test_student_can_submit_booking()
    {
        $monday = date('Y-m-d', strtotime('next Monday'));

        $response = $this->post(route('student.store'), [
            'student_name' => 'Budi Santoso',
            'student_email' => 'budi@student.ac.id',
            'nim' => '21010199',
            'department' => 'Teknik Informatika',
            'purpose' => 'Bimbingan',
            'notes' => 'Diskusi Bab 1 dan Bab 2',
            'appointment_date' => $monday,
            'time_slot' => '09:00 - 10:00',
        ]);

        $response->assertRedirect(route('student.index'));
        $response->assertSessionHas('success_booking');

        $this->assertDatabaseHas('appointments', [
            'student_name' => 'Budi Santoso',
            'student_email' => 'budi@student.ac.id',
            'nim' => '21010199',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_login_and_approve_appointment()
    {
        $appointment = Appointment::create([
            'user_id' => $this->user->id,
            'booking_code' => 'BMB-20260722-TEST',
            'token' => 'testtoken123',
            'student_name' => 'Siti Rahma',
            'student_email' => 'siti@student.ac.id',
            'nim' => '21010200',
            'department' => 'Sistem Informasi',
            'purpose' => 'Tanda Tangan',
            'appointment_date' => date('Y-m-d', strtotime('next Monday')),
            'time_slot' => '09:00 - 10:00',
            'status' => 'pending',
        ]);

        // Login as Admin
        $response = $this->actingAs($this->user)->post(route('admin.appointments.approve', $appointment->id));
        $response->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reschedule_appointment()
    {
        $appointment = Appointment::create([
            'user_id' => $this->user->id,
            'booking_code' => 'BMB-20260722-RESCH',
            'token' => 'tokenresch123',
            'student_name' => 'Dewi Lestari',
            'student_email' => 'dewi@student.ac.id',
            'nim' => '21010201',
            'department' => 'Teknik Informatika',
            'purpose' => 'Bimbingan',
            'appointment_date' => date('Y-m-d', strtotime('next Monday')),
            'time_slot' => '09:00 - 10:00',
            'status' => 'pending',
        ]);

        $nextTuesday = date('Y-m-d', strtotime('next Tuesday'));

        $response = $this->actingAs($this->user)->post(route('admin.appointments.reschedule', $appointment->id), [
            'proposed_date' => $nextTuesday,
            'proposed_time_slot' => '13:00 - 14:00',
            'reason' => 'Ada rapat fakultas dadakan.',
        ]);

        $response->assertRedirect();

        $appointment->refresh();
        $this->assertEquals('rescheduled', $appointment->status);
        $this->assertEquals($nextTuesday, $appointment->proposed_date->toDateString());
        $this->assertEquals('13:00 - 14:00', $appointment->proposed_time_slot);
        $this->assertEquals('Ada rapat fakultas dadakan.', $appointment->reschedule_reason);
    }
}
