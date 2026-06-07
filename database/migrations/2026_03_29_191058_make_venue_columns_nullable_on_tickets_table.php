<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // venue_requested is already a nullable foreignId on venues (see create_tickets_table).
    }

    public function down(): void
    {
        //
    }
};
