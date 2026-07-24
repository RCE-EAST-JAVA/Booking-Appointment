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
        // Drop foreign keys to users table because users are managed in the external rce_db connection
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('blocked_dates', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('date_overrides', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('blocked_dates', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('date_overrides', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
