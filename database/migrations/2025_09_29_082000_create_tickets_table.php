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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id('ticket_id');
            $table->string('ticket_number');
            $table->foreignId('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreignId('event_type_id')->references('event_type_id')->on('event__types')->onDelete('cascade');
            $table->string('title');
            $table->string('description');
            $table->integer('plv_participants');
            $table->integer('external_participants');
            $table->integer('total_participants');
            $table->string('sponsoring_body')->nullable();
            $table->string('venue_requested');
            $table->string('alternate_venue')->nullable();
            $table->string('special_requirements')->nullable();
            $table->string('date-from');
            $table->string('date-to');
            $table->string('time-from');
            $table->string('time-to');
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
