<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmationMail;
use App\Models\Announcement;
use App\Models\Appointment;
use App\Models\BlockedDate;
use App\Models\DateOverride;
use App\Models\Schedule;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index()
    {
        $lecturers = User::where('sync_bimbingan', true)->where('role', '!=', 'admin')->get();
        $announcement = Announcement::where('is_active', true)->first();
        return view('student.index', compact('lecturers', 'announcement'));
    }

    public function getAvailableSlots(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'user_id' => 'nullable|exists:rce_db.users,id',
        ]);

        $date = Carbon::parse($request->date);
        $userId = $request->user_id 
            ?? User::where('sync_bimbingan', true)->where('role', '!=', 'admin')->first()?->id 
            ?? User::first()?->id;
        $dateStr = $date->toDateString();
        $dayOfWeek = $date->dayOfWeek; // 0 (Sun) - 6 (Sat)

        // 1. Check DateOverride table
        $override = DateOverride::whereDate('date', $dateStr)
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->first();

        if ($override) {
            if (!$override->is_available) {
                return response()->json([
                    'is_blocked' => true,
                    'reason' => $override->reason ?: 'Tanggal ini diatur sebagai Hari Libur / Tutup Bimbingan.',
                    'slots' => [],
                ]);
            }
        } else {
            // Check legacy BlockedDate table
            $isBlocked = BlockedDate::whereDate('date', $dateStr)
                ->where(function ($q) use ($userId) {
                    $q->whereNull('user_id')->orWhere('user_id', $userId);
                })
                ->first();

            if ($isBlocked) {
                return response()->json([
                    'is_blocked' => true,
                    'reason' => $isBlocked->reason ?: 'Tanggal ini diblokir / Dosen Berhalangan.',
                    'slots' => [],
                ]);
            }

            // Default weekend check if no override exists
            if ($dayOfWeek === 0 || $dayOfWeek === 6) {
                return response()->json([
                    'is_blocked' => true,
                    'reason' => 'Hari ' . ($dayOfWeek === 0 ? 'Minggu' : 'Sabtu') . ' secara default libur / tidak melayani bimbingan.',
                    'slots' => [],
                ]);
            }
        }

        // Fetch schedules
        $schedules = Schedule::where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();

        // If weekend override is enabled but no weekend schedules defined, fallback to active schedules
        if ($schedules->isEmpty() && ($dayOfWeek === 0 || $dayOfWeek === 6)) {
            $schedules = Schedule::where('is_active', true)
                ->get()
                ->unique('time_slot');
        }

        $unavailableSlots = $override ? ($override->unavailable_slots ?? []) : [];
        $slots = [];

        foreach ($schedules as $schedule) {
            $isSlotDisabledByOverride = in_array($schedule->time_slot, $unavailableSlots);

            if (!$isSlotDisabledByOverride && $override) {
                $parts = explode('-', $schedule->time_slot);
                if (count($parts) === 2) {
                    $slotStart = trim($parts[0]);
                    $slotEnd = trim($parts[1]);
                    try {
                        $slotStartMin = Carbon::parse($slotStart)->format('H:i');
                        $slotEndMin = Carbon::parse($slotEnd)->format('H:i');

                        // 1. Check single legacy range
                        if ($override->unavailable_start && $override->unavailable_end) {
                            $unavailStartMin = Carbon::parse($override->unavailable_start)->format('H:i');
                            $unavailEndMin = Carbon::parse($override->unavailable_end)->format('H:i');
                            if (($slotStartMin < $unavailEndMin) && ($slotEndMin > $unavailStartMin)) {
                                $isSlotDisabledByOverride = true;
                            }
                        }

                        // 2. Check multiple new ranges
                        if (!$isSlotDisabledByOverride && !empty($override->unavailable_ranges)) {
                            foreach ($override->unavailable_ranges as $range) {
                                $rStart = $range['start'] ?? null;
                                $rEnd = $range['end'] ?? null;
                                if ($rStart && $rEnd) {
                                    $unavailStartMin = Carbon::parse($rStart)->format('H:i');
                                    $unavailEndMin = Carbon::parse($rEnd)->format('H:i');
                                    if (($slotStartMin < $unavailEndMin) && ($slotEndMin > $unavailStartMin)) {
                                        $isSlotDisabledByOverride = true;
                                        break;
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                    }
                }
            }

            $bookedCount = Appointment::whereDate('appointment_date', $dateStr)
                ->where('time_slot', $schedule->time_slot)
                ->whereIn('status', ['pending', 'approved', 'rescheduled'])
                ->where(function ($q) use ($userId) {
                    $q->whereNull('user_id')->orWhere('user_id', $userId);
                })
                ->count();

            $remainingQuota = $isSlotDisabledByOverride ? 0 : max(0, $schedule->quota - $bookedCount);

            $slots[] = [
                'time_slot' => $schedule->time_slot,
                'quota' => $schedule->quota,
                'booked' => $bookedCount,
                'remaining' => $remainingQuota,
                'is_available' => !$isSlotDisabledByOverride && $remainingQuota > 0,
                'disabled_reason' => $isSlotDisabledByOverride ? 'Jam Diblokir (Rapat/Dinas)' : null,
            ];
        }

        return response()->json([
            'is_blocked' => false,
            'slots' => $slots,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_name' => 'required|string|max:255',
            'student_email' => 'required|email|max:255',
            'nim' => 'required|string|max:50',
            'department' => 'required|string|max:255',
            'purpose' => 'required|in:Bimbingan,Tanda Tangan,Lain-lain',
            'notes' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:5120',
            'appointment_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string',
            'user_id' => 'nullable|exists:rce_db.users,id',
        ]);

        $date = Carbon::parse($validated['appointment_date'])->toDateString();
        $userId = $validated['user_id'] 
            ?? User::where('sync_bimbingan', true)->where('role', '!=', 'admin')->first()?->id 
            ?? User::first()?->id;

        // Verify DateOverride
        $override = DateOverride::whereDate('date', $date)
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->first();

        if ($override && !$override->is_available) {
            return back()->withInput()->with('error', 'Tanggal ini diatur sebagai hari libur / tutup bimbingan oleh dosen.');
        }

        $isSlotDisabledByRange = false;
        if ($override) {
            $parts = explode('-', $validated['time_slot']);
            if (count($parts) === 2) {
                $slotStart = trim($parts[0]);
                $slotEnd = trim($parts[1]);
                try {
                    $slotStartMin = Carbon::parse($slotStart)->format('H:i');
                    $slotEndMin = Carbon::parse($slotEnd)->format('H:i');

                    // 1. Check legacy single range
                    if ($override->unavailable_start && $override->unavailable_end) {
                        $unavailStartMin = Carbon::parse($override->unavailable_start)->format('H:i');
                        $unavailEndMin = Carbon::parse($override->unavailable_end)->format('H:i');
                        if (($slotStartMin < $unavailEndMin) && ($slotEndMin > $unavailStartMin)) {
                            $isSlotDisabledByRange = true;
                        }
                    }

                    // 2. Check multiple new ranges
                    if (!$isSlotDisabledByRange && !empty($override->unavailable_ranges)) {
                        foreach ($override->unavailable_ranges as $range) {
                            $rStart = $range['start'] ?? null;
                            $rEnd = $range['end'] ?? null;
                            if ($rStart && $rEnd) {
                                $unavailStartMin = Carbon::parse($rStart)->format('H:i');
                                $unavailEndMin = Carbon::parse($rEnd)->format('H:i');
                                if (($slotStartMin < $unavailEndMin) && ($slotEndMin > $unavailStartMin)) {
                                    $isSlotDisabledByRange = true;
                                    break;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                }
            }
        }

        if (($override && in_array($validated['time_slot'], $override->unavailable_slots ?? [])) || $isSlotDisabledByRange) {
            return back()->withInput()->with('error', 'Slot waktu ' . $validated['time_slot'] . ' WIB pada tanggal tersebut sedang tidak tersedia (berhalangan/rapat).');
        }

        // Verify quota
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $schedule = Schedule::where('day_of_week', $dayOfWeek)
            ->where('time_slot', $validated['time_slot'])
            ->where('is_active', true)
            ->first();

        // Fallback for weekend override
        if (!$schedule && ($dayOfWeek === 0 || $dayOfWeek === 6) && $override && $override->is_available) {
            $schedule = Schedule::where('time_slot', $validated['time_slot'])
                ->where('is_active', true)
                ->first();
        }

        if (!$schedule) {
            return back()->withInput()->with('error', 'Slot waktu yang dipilih tidak tersedia pada jadwal dosen.');
        }

        $bookedCount = Appointment::whereDate('appointment_date', $date)
            ->where('time_slot', $validated['time_slot'])
            ->whereIn('status', ['pending', 'approved', 'rescheduled'])
            ->count();

        if ($bookedCount >= $schedule->quota) {
            return back()->withInput()->with('error', 'Kuota untuk slot waktu tersebut telah penuh. Silakan pilih slot atau tanggal lain.');
        }

        // Handle attachment file
        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('attachments', 'public');
        }

        // Generate unique 7-digit alphanumeric booking code & token
        do {
            $bookingCode = strtoupper(Str::random(7));
        } while (Appointment::where('booking_code', $bookingCode)->exists());
        
        $token = Str::random(32);

        $appointment = Appointment::create([
            'user_id' => $userId,
            'booking_code' => $bookingCode,
            'token' => $token,
            'student_name' => $validated['student_name'],
            'student_email' => $validated['student_email'],
            'nim' => $validated['nim'],
            'department' => $validated['department'],
            'purpose' => $validated['purpose'],
            'notes' => $validated['notes'] ?? null,
            'file_path' => $filePath,
            'appointment_date' => $date,
            'time_slot' => $validated['time_slot'],
            'status' => 'pending',
        ]);

        // Send booking confirmation email asynchronously or directly
        NotificationService::send($appointment->student_email, new BookingConfirmationMail($appointment), $appointment->id);

        return redirect()->route('student.index')
            ->with('success_booking', [
                'code' => $bookingCode,
                'name' => $appointment->student_name,
                'date' => Carbon::parse($date)->translatedFormat('l, d F Y'),
                'slot' => $appointment->time_slot,
            ]);
    }

    public function trackStatus(Request $request)
    {
        $search = trim($request->input('search'));
        $appointments = collect();

        if ($search) {
            $appointments = Appointment::with('user')
                ->where('nim', $search)
                ->orWhere('booking_code', $search)
                ->latest()
                ->get();
        }

        return view('student.tracker', compact('appointments', 'search'));
    }

    public function showRescheduleAction(Request $request, string $token)
    {
        $appointment = Appointment::where('token', $token)->firstOrFail();
        $action = $request->query('action');

        return view('student.reschedule_action', compact('appointment', 'action'));
    }

    public function handleRescheduleAction(Request $request, string $token)
    {
        $request->validate([
            'action' => 'required|in:accept,cancel',
        ]);

        $appointment = Appointment::where('token', $token)->firstOrFail();

        if ($appointment->status !== 'rescheduled') {
            return redirect()->route('student.tracker', ['search' => $appointment->booking_code])
                ->with('info', 'Status pengajuan janji ini telah diperbarui.');
        }

        if ($request->action === 'accept') {
            $appointment->update([
                'status' => 'approved',
                'appointment_date' => $appointment->proposed_date ?? $appointment->appointment_date,
                'time_slot' => $appointment->proposed_time_slot ?? $appointment->time_slot,
                'proposed_date' => null,
                'proposed_time_slot' => null,
            ]);

            $message = 'Terima kasih! Anda telah menyetujui jadwal baru janji bimbingan.';
        } else {
            $appointment->update([
                'status' => 'cancelled',
            ]);

            $message = 'Permohonan bimbingan telah dibatalkan.';
        }

        return redirect()->route('student.tracker', ['search' => $appointment->booking_code])
            ->with('status_updated', $message);
    }
}
