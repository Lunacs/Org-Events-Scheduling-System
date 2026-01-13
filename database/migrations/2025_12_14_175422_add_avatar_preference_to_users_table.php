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
        Schema::table('users', function (Blueprint $table) {
            // Avatar preference: 'uploaded' to use the uploaded photo, 'dicebear' to use DiceBear avatar
            // Default is 'dicebear' so existing users keep their current behavior
            $table->string('avatar_preference', 20)->default('dicebear')->after('avatar_seed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_preference');
        });
    }
};
