<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a JSON column that stores all outsourced-transport vehicle entries
     * as an array of objects. The existing flat columns (oc_driver_name, etc.)
     * are kept for backward-compatibility with legacy records.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->json('oc_vehicles')->nullable()->after('oc_driver_contact_number');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('oc_vehicles');
        });
    }
};
