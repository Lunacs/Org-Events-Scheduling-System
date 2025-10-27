<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter the enum to include 'forwarded' and 'revision_requested'
        DB::statement("ALTER TABLE `o_s_a__approvals` MODIFY COLUMN `decision` ENUM('pending', 'approved', 'rejected', 'forwarded', 'revision_requested') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE `o_s_a__approvals` MODIFY COLUMN `decision` ENUM('pending', 'approved', 'rejected') NOT NULL");
    }
};
