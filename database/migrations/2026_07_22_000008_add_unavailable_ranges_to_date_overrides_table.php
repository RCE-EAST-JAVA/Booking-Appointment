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
            $table->json('unavailable_ranges')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('date_overrides', function (Blueprint $table) {
            $table->dropColumn('unavailable_ranges');
        });
    }
};
