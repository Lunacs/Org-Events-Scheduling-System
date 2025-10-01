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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_type_id')->constrained('event_type')->onDelete('cascade');
            $table->string('title');
            $table->string('description');
            $table->string('venue-requested');
            $table->string('date-requested');
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
