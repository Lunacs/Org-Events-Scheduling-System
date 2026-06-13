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
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('status');
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::table('event_schedules', function (Blueprint $table) {
            $table->index('start_date');
            $table->index('start_time');
            $table->index('venue');
        });

        Schema::table('o_s_a__approvals', function (Blueprint $table) {
            $table->index('ticket_id');
        });

        Schema::table('office__approvals', function (Blueprint $table) {
            $table->index('ticket_id');
            $table->index('office_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('event_schedules', function (Blueprint $table) {
            $table->dropIndex(['start_date']);
            $table->dropIndex(['start_time']);
            $table->dropIndex(['venue']);
        });

        Schema::table('o_s_a__approvals', function (Blueprint $table) {
            $table->dropIndex(['ticket_id']);
        });

        Schema::table('office__approvals', function (Blueprint $table) {
            $table->dropIndex(['ticket_id']);
            $table->dropIndex(['office_id']);
        });
    }
};
