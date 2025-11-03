<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds performance indexes to lookup/reference tables.
     * These tables are frequently used in dropdowns, filters, and sorting.
     *
     * Note: Core operational tables (users, tickets, events, event_schedules, notifications,
     * ticket_comments, transaction_logs, attachments, student__organizations) already have
     * comprehensive indexes from previous migrations.
     */
    public function up(): void
    {
        // ===== EVENT_TYPES TABLE =====
        // Used in dropdowns and event filtering
        Schema::table('event__types', function (Blueprint $table) {
            // Type name sorting (dropdowns, event listings)
            $table->index('type_name', 'event_types_name_idx');
        });

        // ===== FUND_SOURCES TABLE =====
        // Used in dropdowns and budget reports
        Schema::table('fund__sources', function (Blueprint $table) {
            // Source name sorting (dropdowns)
            $table->index('source_name', 'fund_sources_name_idx');
        });

        // ===== OFFICES TABLE =====
        // Used in office approval routing and listings
        Schema::table('offices', function (Blueprint $table) {
            // Office name sorting (dropdowns, listings)
            $table->index('office_name', 'offices_name_idx');
        });

        // ===== POSITIONS TABLE =====
        // Used in user profile and listings
        Schema::table('positions', function (Blueprint $table) {
            // Position name sorting (dropdowns)
            $table->index('position_name', 'positions_name_idx');
        });

        // ===== ROLES TABLE =====
        // Core table for access control
        Schema::table('roles', function (Blueprint $table) {
            // Role name lookups and sorting
            $table->index('role_name', 'roles_name_idx');
        });

        // ===== COURSES TABLE =====
        // Used in organization and student filtering
        Schema::table('courses', function (Blueprint $table) {
            // Course name sorting (dropdowns)
            $table->index('course_name', 'courses_name_idx');

            // Course code lookups
            $table->index('course_code', 'courses_code_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop only the indexes we created (lookup tables only)

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('courses_name_idx');
            $table->dropIndex('courses_code_idx');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex('roles_name_idx');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropIndex('positions_name_idx');
        });

        Schema::table('offices', function (Blueprint $table) {
            $table->dropIndex('offices_name_idx');
        });

        Schema::table('fund__sources', function (Blueprint $table) {
            $table->dropIndex('fund_sources_name_idx');
        });

        Schema::table('event__types', function (Blueprint $table) {
            $table->dropIndex('event_types_name_idx');
        });
    }
};
