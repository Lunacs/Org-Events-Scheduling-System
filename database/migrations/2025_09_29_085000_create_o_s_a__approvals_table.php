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
        Schema::create(table: 'o_s_a__approvals', callback: function (Blueprint $table) {
            $table->id('osa_approval_id');
            $table->foreignId('ticket_id')->references('ticket_id')->on('tickets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->references('user_id')->on('users')->onDelete('cascade');
            $table->enum('decision', ['pending', 'approved', 'rejected']);
            $table->string('remarks', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('o_s_a__approvals');
    }
};
