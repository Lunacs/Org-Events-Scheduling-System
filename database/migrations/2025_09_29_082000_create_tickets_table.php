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
            $table->text('description');
            $table->string('proponent_contact');
            $table->string('adviser_contact');
            $table->integer('plv_participants');
            $table->integer('external_participants')->nullable();
            $table->integer('total_participants');
            $table->foreignId('venue_requested')->nullable()->references('venue_id')->on('venues')->onDelete('set null');
            $table->string('venue_other')->nullable();
            $table->foreignId('alternate_venue')->nullable()->references('venue_id')->on('venues')->onDelete('set null');
            $table->string('alternate_venue_other')->nullable();
            $table->string('special_requirements')->nullable();
            $table->boolean('igp_requested')->default(false);
            $table->string('igp_details')->nullable();
            $table->string('oc_accommodation')->nullable();
            $table->enum('oc_tsp', ['in-house', 'outsourced'])->nullable();
            $table->string('oc_driver_name')->nullable();
            $table->string('oc_transportation_type')->nullable();
            $table->string('oc_vehicle_plate_number')->nullable();
            $table->string('oc_driver_contact_number')->nullable();
            $table->string('date_from');
            $table->string('date_to');
            $table->string('time_from');
            $table->string('time_to');
            $table->float('estimated_budget')->nullable();
            $table->text('budget_breakdown')->nullable();
            $table->text('additional_notes')->nullable();
            $table->foreignId('fund_source_id')->references('source_id')->on('fund__sources')->onDelete('cascade');
            $table->enum('status', ['received', 'gso_review', 'pending_osa_approval', 'for_revision', 'approved', 'amended', 'completed'])->default('received');
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
