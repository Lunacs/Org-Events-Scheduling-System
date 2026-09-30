<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaction_logs', function (Blueprint $table): void {
            $table->text('details')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('transaction_logs')
            ->whereRaw('CHAR_LENGTH(`details`) > ?', [255])
            ->exists()
        ) {
            throw new RuntimeException(
                'Cannot roll back the transaction log details migration because rows exceed 255 characters.',
            );
        }

        Schema::table('transaction_logs', function (Blueprint $table): void {
            $table->string('details', 255)->change();
        });
    }
};
