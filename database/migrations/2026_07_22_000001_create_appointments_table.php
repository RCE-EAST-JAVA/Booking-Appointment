<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('booking_code')->unique();
            $table->string('token')->unique();
            $table->string('student_name');
            $table->string('student_email');
            $table->string('nim')->index();
            $table->string('department');
            $table->enum('purpose', ['Bimbingan', 'Tanda Tangan', 'Lain-lain']);
            $table->text('notes')->nullable();
            $table->string('file_path')->nullable();
            $table->date('appointment_date');
            $table->string('time_slot');
            $table->enum('status', ['pending', 'approved', 'rescheduled', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->text('reschedule_reason')->nullable();
            $table->date('proposed_date')->nullable();
            $table->string('proposed_time_slot')->nullable();
            $table->timestamps();

            // Composite performance index for quota checking
            $table->index(['appointment_date', 'time_slot']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
