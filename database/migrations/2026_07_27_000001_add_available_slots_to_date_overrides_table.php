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
        Schema::table('date_overrides', function (Blueprint $table) {
            if (!Schema::hasColumn('date_overrides', 'available_slots')) {
                $table->json('available_slots')->nullable()->after('unavailable_ranges');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('date_overrides', function (Blueprint $table) {
            if (Schema::hasColumn('date_overrides', 'available_slots')) {
                $table->dropColumn('available_slots');
            }
        });
    }
};
