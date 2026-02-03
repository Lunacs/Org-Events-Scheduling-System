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
        Schema::create('content_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key')->unique();     // e.g., 'announcements', 'terms_conditions'
            $table->string('section_type');               // 'announcement', 'terms_conditions', etc.
            $table->string('title');                      // Display title
            $table->longText('content')->nullable();      // Rich text HTML content
            $table->boolean('is_active')->default(true);  // Toggle visibility
            $table->integer('display_order')->default(0); // For ordering multiple sections
            $table->json('target_roles')->nullable();
            $table->timestamps();

            $table->index(['section_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_sections');
    }
};
