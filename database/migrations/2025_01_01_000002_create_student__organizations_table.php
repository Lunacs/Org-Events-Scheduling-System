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
        Schema::create('student__organizations', function (Blueprint $table) {
            $table->id('org_id');
            $table->string('org_code');
            $table->string('org_name');
            $table->foreignId('course_id')->references('course_id')->on('courses')->onDelete('cascade');
            $table->string('adviser_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student__organizations');
    }
};
