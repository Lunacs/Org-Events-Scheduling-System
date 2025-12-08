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
        Schema::create('approval_history', function (Blueprint $table) {
            $table->id('history_id');
            $table->foreignId('ticket_id')->references('ticket_id')->on('tickets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->references('user_id')->on('users')->onDelete('set null');
            $table->foreignId('office_id')->nullable()->references('office_id')->on('offices')->onDelete('set null');
            $table->enum('approval_type', ['osa', 'office'])->comment('Type of approval: OSA or Office (GSO, etc.)');
            $table->enum('action', ['pending', 'approved', 'for_revision', 'forwarded'])->comment('The action taken');
            $table->text('remarks')->nullable()->comment('Remarks or notes for this action');
            $table->string('office_name', 255)->nullable()->comment('Cached office name for display (in case office is deleted)');
            $table->timestamps();

            // Indexes for performance
            $table->index('ticket_id');
            $table->index('user_id');
            $table->index('office_id');
            $table->index('approval_type');
            $table->index('action');
            $table->index('created_at');
            $table->index(['ticket_id', 'created_at']); // Composite index for timeline queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_history');
    }
};
