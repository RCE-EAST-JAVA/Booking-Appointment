<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentApprovedMail;
use App\Mail\AppointmentRejectedMail;
use App\Mail\AppointmentRescheduledMail;
use App\Mail\TestSmtpMail;
use App\Models\Announcement;
use App\Models\Appointment;
use App\Models\BlockedDate;
use App\Models\DateOverride;
use App\Models\EmailLog;
use App\Models\Schedule;
use App\Models\SmtpSetting;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\SmtpConfigService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    // --- Auth ---
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$fieldType => $loginInput, 'password' => $request->password], $request->boolean('remember', true))) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'username' => 'Kombinasi username dan password tidak sesuai.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    // --- Dashboard & Appointments ---
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $query = Appointment::with('user');

        // Apply filters
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('purpose')) {
            $query->where('purpose', $request->purpose);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('booking_code', 'like', "%{$search}%");
            });
        }

        $appointments = $query->latest()->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'today' => Appointment::whereDate('appointment_date', Carbon::today())->count(),
            'pending' => Appointment::where('status', 'pending')->count(),
            'approved' => Appointment::where('status', 'approved')->count(),
            'rescheduled' => Appointment::where('status', 'rescheduled')->count(),
            'completed' => Appointment::where('status', 'completed')->count(),
        ];

        // Calendar view data payload
        $calendarEvents = Appointment::select('id', 'student_name', 'booking_code', 'appointment_date', 'time_slot', 'status', 'purpose')
            ->get()
            ->map(function ($apt) {
                return [
                    'id' => $apt->id,
                    'title' => $apt->student_name . ' (' . $apt->time_slot . ')',
                    'start' => $apt->appointment_date->toDateString(),
                    'status' => $apt->status,
                    'purpose' => $apt->purpose,
                    'code' => $apt->booking_code,
                ];
            });

        // Today & Upcoming appointments overview
        $todayAppointments = Appointment::with('user')
            ->whereDate('appointment_date', Carbon::today())
            ->orderBy('time_slot')
            ->get();

        $upcomingAppointments = Appointment::with('user')
            ->whereDate('appointment_date', '>', Carbon::today())
            ->whereIn('status', ['pending', 'approved', 'rescheduled'])
            ->orderBy('appointment_date', 'asc')
            ->orderBy('time_slot', 'asc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('appointments', 'stats', 'calendarEvents', 'todayAppointments', 'upcomingAppointments'));
    }

    public function approveAppointment($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => 'approved',
        ]);

        NotificationService::send($appointment->student_email, new AppointmentApprovedMail($appointment), $appointment->id);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => "Janji Bimbingan ({$appointment->booking_code}) berhasil disetujui."]);
        }

        return back()->with('success', "Janji Bimbingan ({$appointment->booking_code}) berhasil disetujui.");
    }

    public function rejectAppointment(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => 'rejected',
            'reschedule_reason' => $request->reason,
        ]);

        NotificationService::send($appointment->student_email, new AppointmentRejectedMail($appointment), $appointment->id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => "Janji Bimbingan ({$appointment->booking_code}) telah ditolak."]);
        }

        return back()->with('success', "Janji Bimbingan ({$appointment->booking_code}) telah ditolak.");
    }

    public function completeAppointment($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => 'completed',
        ]);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => "Janji Bimbingan ({$appointment->booking_code}) ditandai selesai."]);
        }

        return back()->with('success', "Janji Bimbingan ({$appointment->booking_code}) ditandai selesai.");
    }

    public function rescheduleAppointment(Request $request, $id)
    {
        $request->validate([
            'proposed_date' => 'required|date|after_or_equal:today',
            'proposed_time_slot' => 'required|string',
            'reason' => 'required|string|max:500',
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => 'rescheduled',
            'proposed_date' => $request->proposed_date,
            'proposed_time_slot' => $request->proposed_time_slot,
            'reschedule_reason' => $request->reason,
            'token' => Str::random(32), // ensure fresh token
        ]);

        NotificationService::send($appointment->student_email, new AppointmentRescheduledMail($appointment), $appointment->id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => "Usulan perubahan jadwal untuk ({$appointment->booking_code}) berhasil dikirim ke mahasiswa."]);
        }

        return back()->with('success', "Usulan perubahan jadwal untuk ({$appointment->booking_code}) berhasil dikirim ke mahasiswa.");
    }

    // --- Schedules, Blocked Dates & Announcement ---
    public function schedulesIndex(Request $request)
    {
        $schedules = Schedule::orderBy('day_of_week')->orderBy('time_slot')->get();
        $blockedDates = BlockedDate::orderBy('date', 'desc')->get();
        
        $dayNames = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        $announcement = Announcement::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'is_active' => false,
                'message' => 'Pengumuman: Harap membawa draf berkas/laporan fisik saat menghadiri sesi bimbingan.',
                'type' => 'info',
            ]
        );

        $query = Appointment::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('purpose')) {
            $query->where('purpose', $request->purpose);
        }
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('booking_code', 'like', "%{$search}%");
            });
        }

        $appointments = $query->orderBy('appointment_date', 'desc')->paginate(15)->withQueryString();

        return view('admin.schedules', compact('schedules', 'blockedDates', 'dayNames', 'announcement', 'appointments'));
    }

    public function syncHolidays(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $userId = Auth::id();
        $importedCount = 0;

        try {
            // Priority 1: GitHub guangrei APIHariLibur_V2 (Sangat Lengkap + Cuti Bersama Indonesia!)
            $response = Http::timeout(10)->withOptions(['verify' => false])->get("https://raw.githubusercontent.com/guangrei/APIHariLibur_V2/main/holidays.json");
            $data = $response->json();

            if (is_array($data)) {
                foreach ($data as $dateKey => $val) {
                    if ($dateKey === 'info' || !is_array($val)) continue;
                    
                    // Filter berdasarkan tahun yang diminta
                    if (str_starts_with($dateKey, (string)$year)) {
                        $reason = $val['summary'] ?? $val['keterangan'] ?? 'Libur Nasional & Cuti Bersama';

                        DateOverride::updateOrCreate(
                            [
                                'user_id' => $userId,
                                'date' => $dateKey,
                            ],
                            [
                                'is_available' => false,
                                'reason' => $reason,
                            ]
                        );
                        $importedCount++;
                    }
                }
            }

            // Fallback 2: nager.at API
            if ($importedCount === 0) {
                $response = Http::timeout(10)->withOptions(['verify' => false])->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/ID");
                $data = $response->json();
                if (is_array($data)) {
                    foreach ($data as $item) {
                        $dateStr = $item['date'] ?? null;
                        $reason = $item['localName'] ?? $item['name'] ?? 'Libur Nasional';
                        if ($dateStr) {
                            DateOverride::updateOrCreate(
                                [
                                    'user_id' => $userId,
                                    'date' => $dateStr,
                                ],
                                [
                                    'is_available' => false,
                                    'reason' => $reason,
                                ]
                            );
                            $importedCount++;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'count' => $importedCount,
                'message' => "Berhasil mengimpor {$importedCount} tanggal merah & cuti bersama nasional Indonesia tahun {$year}!",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dari API Hari Libur & Cuti Bersama: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateAnnouncement(Request $request)
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
            'message' => 'nullable|string|max:1000',
        ]);

        Announcement::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'is_active' => $validated['is_active'],
                'message' => $validated['message'],
            ]
        );

        return back()->with('success', 'Pengaturan pengumuman registrasi jadwal berhasil disimpan!');
    }

    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'time_slot' => 'required|string|max:50',
            'quota' => 'required|integer|min:1',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['is_active'] = true;

        Schedule::create($validated);

        return back()->with('success', 'Jadwal & Kuota baru berhasil ditambahkan.');
    }

    public function updateSchedule(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        
        $validated = $request->validate([
            'quota' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        $schedule->update($validated);

        return back()->with('success', 'Perubahan kuota & status jadwal berhasil disimpan.');
    }

    public function deleteSchedule($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return back()->with('success', 'Jadwal berhasil dihapus.');
    }

    public function storeBlockedDate(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = Auth::id();

        BlockedDate::create($validated);

        return back()->with('success', 'Tanggal libur / blokir berhasil ditambahkan.');
    }

    public function deleteBlockedDate($id)
    {
        $blocked = BlockedDate::findOrFail($id);
        $blocked->delete();

        return back()->with('success', 'Tanggal blokir berhasil dihapus.');
    }

    // --- SMTP Configuration Panel ---
    public function smtpIndex()
    {
        $setting = SmtpSetting::first() ?? new SmtpSetting([
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'from_email' => 'no-reply@portal.ac.id',
            'from_name' => 'Portal Bimbingan',
            'is_active' => true,
        ]);

        $emailLogs = EmailLog::with('appointment')->latest()->take(20)->get();

        return view('admin.smtp', compact('setting', 'emailLogs'));
    }

    public function updateSmtp(Request $request)
    {
        $validated = $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'encryption' => 'required|in:tls,ssl,none',
            'from_email' => 'required|email',
            'from_name' => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        $setting = SmtpSetting::first();
        if (!$setting) {
            $setting = new SmtpSetting();
        }

        $setting->host = $validated['host'];
        $setting->port = $validated['port'];
        $setting->username = $validated['username'] ?? null;
        if (!empty($validated['password'])) {
            $setting->password = $validated['password'];
        }
        $setting->encryption = $validated['encryption'];
        $setting->from_email = $validated['from_email'];
        $setting->from_name = $validated['from_name'];
        $setting->is_active = $validated['is_active'];
        $setting->save();

        return back()->with('success', 'Pengaturan SMTP server berhasil diperbarui!');
    }

    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $sent = NotificationService::send($request->test_email, new TestSmtpMail());

        if ($sent) {
            return back()->with('success', "Email tes berhasil dikirim ke {$request->test_email}!");
        }

        $lastError = EmailLog::latest()->first()?->error_message ?? 'Koneksi ke SMTP server gagal.';
        return back()->with('error', "Gagal mengirim email tes: " . $lastError);
    }

    // --- Profile & Password Settings ---
    public function profileIndex()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,email,' . $user->id,
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['username'];
        $user->save();

        return back()->with('success', 'Informasi profil berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return back()->with('success', 'Password berhasil diubah!');
    }

    // --- Calendar Tiles & Date Overrides ---
    public function getCalendarMonthData(Request $request)
    {
        $monthStr = $request->query('month', date('Y-m')); // 'YYYY-MM'
        try {
            $firstDay = Carbon::parse($monthStr . '-01');
        } catch (\Exception $e) {
            $firstDay = Carbon::today()->firstOfMonth();
        }

        $daysInMonth = $firstDay->daysInMonth;
        $userId = Auth::id();

        // Fetch overrides for this month
        $overrides = DateOverride::whereYear('date', $firstDay->year)
            ->whereMonth('date', $firstDay->month)
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->get()
            ->keyBy(fn ($item) => $item->date->format('Y-m-d'));

        // Fetch blocked dates for legacy compatibility
        $blockedDates = BlockedDate::whereYear('date', $firstDay->year)
            ->whereMonth('date', $firstDay->month)
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->get()
            ->keyBy(fn ($item) => $item->date->format('Y-m-d'));

        // Fetch appointments for this month grouped by formatted Y-m-d date
        $monthAppointments = Appointment::whereYear('appointment_date', $firstDay->year)
            ->whereMonth('appointment_date', $firstDay->month)
            ->whereIn('status', ['pending', 'approved', 'rescheduled', 'completed'])
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->appointment_date)->format('Y-m-d');
            });

        // Master default schedule slots
        $schedules = Schedule::where(function ($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->where('is_active', true)
            ->get();

        $masterTimeSlots = $schedules->pluck('time_slot')->unique()->sort()->values()->toArray();
        if (empty($masterTimeSlots)) {
            $masterTimeSlots = ['08:00 - 09:00', '09:00 - 10:00', '10:00 - 11:00', '11:00 - 12:00', '13:00 - 14:00', '14:00 - 15:00'];
        }

        $days = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = Carbon::createFromDate($firstDay->year, $firstDay->month, $day);
            $dateStr = $currentDate->format('Y-m-d');
            $dayOfWeek = $currentDate->dayOfWeek; // 0=Sun, 6=Sat
            $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);

            $override = $overrides->get($dateStr);
            $blocked = $blockedDates->get($dateStr);

            $isAvailable = $isWeekend ? false : true;
            $reason = null;
            $unavailableSlots = [];

            if ($override) {
                $isAvailable = (bool) $override->is_available;
                $reason = $override->reason;
                $unavailableSlots = $override->unavailable_slots ?? [];
            } elseif ($blocked) {
                $isAvailable = false;
                $reason = $blocked->reason ?: 'Hari Libur / Dosen Berhalangan';
            } elseif ($isWeekend) {
                $reason = 'Libur Akhir Pekan (' . ($dayOfWeek === 0 ? 'Minggu' : 'Sabtu') . ')';
            }

            $dayApts = $monthAppointments->get($dateStr, collect([]));
            $bookedCount = $dayApts->count();

            $days[] = [
                'date' => $dateStr,
                'day' => $day,
                'day_name' => $currentDate->translatedFormat('D'),
                'day_of_week' => $dayOfWeek,
                'is_weekend' => $isWeekend,
                'is_today' => $dateStr === Carbon::today()->format('Y-m-d'),
                'is_past' => $dateStr < Carbon::today()->format('Y-m-d'),
                'is_available' => $isAvailable,
                'reason' => $reason,
                'unavailable_slots' => $unavailableSlots,
                'unavailable_start' => $override ? ($override->unavailable_start ? substr($override->unavailable_start, 0, 5) : null) : null,
                'unavailable_end' => $override ? ($override->unavailable_end ? substr($override->unavailable_end, 0, 5) : null) : null,
                'unavailable_ranges' => $override ? ($override->unavailable_ranges ?? []) : [],
                'booked_count' => $bookedCount,
                'appointments' => $dayApts->map(function($apt) {
                    return [
                        'id' => $apt->id,
                        'booking_code' => $apt->booking_code,
                        'student_name' => $apt->student_name,
                        'student_email' => $apt->student_email,
                        'nim' => $apt->nim,
                        'department' => $apt->department,
                        'purpose' => $apt->purpose,
                        'time_slot' => $apt->time_slot,
                        'notes' => $apt->notes,
                        'status' => $apt->status,
                    ];
                })->values()->toArray(),
                'has_override' => $override !== null,
            ];
        }

        return response()->json([
            'month' => $firstDay->format('Y-m'),
            'month_name' => $firstDay->translatedFormat('F Y'),
            'start_day_of_week' => $firstDay->dayOfWeekIso, // 1 (Mon) to 7 (Sun)
            'master_slots' => $masterTimeSlots,
            'days' => $days,
        ]);
    }

    public function saveDateOverride(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'is_available' => 'required|boolean',
            'reason' => 'nullable|string|max:255',
            'unavailable_slots' => 'nullable|array',
            'unavailable_start' => 'nullable|string',
            'unavailable_end' => 'nullable|string',
            'unavailable_ranges' => 'nullable|array',
        ]);

        $start = $validated['unavailable_start'] ?? null;
        $end = $validated['unavailable_end'] ?? null;

        if ($start && $end && $start >= $end) {
            return response()->json([
                'success' => false,
                'message' => 'Jam mulai tidak boleh lebih lambat atau sama dengan jam selesai.'
            ], 422);
        }

        $ranges = $validated['unavailable_ranges'] ?? [];
        foreach ($ranges as $range) {
            $rStart = $range['start'] ?? null;
            $rEnd = $range['end'] ?? null;
            if ($rStart && $rEnd && $rStart >= $rEnd) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jam mulai (' . $rStart . ') tidak boleh lebih lambat atau sama dengan jam selesai (' . $rEnd . ').'
                ], 422);
            }
        }

        $userId = Auth::id();

        DateOverride::updateOrCreate(
            [
                'user_id' => $userId,
                'date' => $validated['date'],
            ],
            [
                'is_available' => $validated['is_available'],
                'reason' => $validated['reason'],
                'unavailable_slots' => $validated['unavailable_slots'] ?? [],
                'unavailable_start' => $start,
                'unavailable_end' => $end,
                'unavailable_ranges' => $ranges,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pengaturan tanggal berhasil disimpan.']);
        }

        return back()->with('success', 'Pengaturan tanggal ' . Carbon::parse($validated['date'])->translatedFormat('d M Y') . ' berhasil disimpan!');
    }

    public function deleteDateOverride(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $userId = Auth::id();
        DateOverride::where('user_id', $userId)->where('date', $validated['date'])->delete();
        BlockedDate::where('user_id', $userId)->where('date', $validated['date'])->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pengaturan tanggal dikembalikan ke default.']);
        }

        return back()->with('success', 'Pengaturan tanggal dikembalikan ke status default!');
    }
}
