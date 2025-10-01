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
        Schema::create('event_schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->dateTime('schedule_date');
            $table->string('schedule_venue');
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->string('remarks', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event__schedules');
    }
};
