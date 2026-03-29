<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert any existing FAQ content sections to reschedule_guidelines type.
     */
    public function up(): void
    {
        DB::table('content_sections')
            ->where('section_type', 'faq')
            ->update(['section_type' => 'reschedule_guidelines']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('content_sections')
            ->where('section_type', 'reschedule_guidelines')
            ->update(['section_type' => 'faq']);
    }
};
