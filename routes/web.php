<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// --- Student Public Portal Routes ---
Route::get('/', [StudentController::class, 'index'])->name('student.index');
Route::get('/available-slots', [StudentController::class, 'getAvailableSlots'])->name('student.available-slots');
Route::post('/book', [StudentController::class, 'store'])->name('student.store');
Route::get('/tracker', [StudentController::class, 'trackStatus'])->name('student.tracker');
Route::get('/reschedule/{token}', [StudentController::class, 'showRescheduleAction'])->name('student.reschedule.show');
Route::post('/reschedule/{token}', [StudentController::class, 'handleRescheduleAction'])->name('student.reschedule.action');

// --- Admin Authentication Routes ---
Route::get('/login', [AdminController::class, 'showLoginForm'])->name('login');
Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// --- Admin Protected Dashboard Routes ---
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Quick Actions
    Route::post('/appointments/{id}/approve', [AdminController::class, 'approveAppointment'])->name('appointments.approve');
    Route::post('/appointments/{id}/reject', [AdminController::class, 'rejectAppointment'])->name('appointments.reject');
    Route::post('/appointments/{id}/complete', [AdminController::class, 'completeAppointment'])->name('appointments.complete');
    Route::post('/appointments/{id}/reschedule', [AdminController::class, 'rescheduleAppointment'])->name('appointments.reschedule');

    // Schedules & Quotas
    Route::get('/schedules', [AdminController::class, 'schedulesIndex'])->name('schedules.index');
    Route::post('/schedules', [AdminController::class, 'storeSchedule'])->name('schedules.store');
    Route::post('/schedules/{id}/update', [AdminController::class, 'updateSchedule'])->name('schedules.update');
    Route::post('/schedules/{id}/delete', [AdminController::class, 'deleteSchedule'])->name('schedules.delete');

    // Blocked Dates / Off Days
    Route::post('/blocked-dates', [AdminController::class, 'storeBlockedDate'])->name('blocked-dates.store');
    Route::post('/blocked-dates/{id}/delete', [AdminController::class, 'deleteBlockedDate'])->name('blocked-dates.delete');

    // Profile & Password Settings
    Route::get('/profile', [AdminController::class, 'profileIndex'])->name('profile.index');
    Route::post('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [AdminController::class, 'updatePassword'])->name('profile.password');

    // Calendar Tiles & Date Overrides
    Route::get('/calendar-month-data', [AdminController::class, 'getCalendarMonthData'])->name('calendar.month-data');
    Route::post('/date-override/save', [AdminController::class, 'saveDateOverride'])->name('date-override.save');
    Route::post('/date-override/delete', [AdminController::class, 'deleteDateOverride'])->name('date-override.delete');
    Route::post('/sync-holidays', [AdminController::class, 'syncHolidays'])->name('sync-holidays');

    // Announcement Settings
    Route::post('/announcement/update', [AdminController::class, 'updateAnnouncement'])->name('announcement.update');

    // SMTP Configuration
    Route::get('/smtp', [AdminController::class, 'smtpIndex'])->name('smtp.index');
    Route::post('/smtp', [AdminController::class, 'updateSmtp'])->name('smtp.update');
    Route::post('/smtp/test', [AdminController::class, 'sendTestEmail'])->name('smtp.test');
});
