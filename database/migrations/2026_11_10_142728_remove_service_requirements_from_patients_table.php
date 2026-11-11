<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'care_type',
                'care_start_date',
                'duration',
                'duration_unit',
                'duty_hours',
                'service_category',
                'service_location',
                'service_location_area',
                'service_city',
                'caregiver_gender',
                'service_details',
                'expected_fee_range',
                'additional_facilities',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Restore columns here if rollback is required.
        });
    }
};
