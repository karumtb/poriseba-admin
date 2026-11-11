<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_service_requirements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();

            // Service Requirement
            $table->string('care_type')->nullable();
            $table->date('care_start_date')->nullable();

            $table->integer('duration')->nullable();
            $table->string('duration_unit')->nullable();

            $table->string('duty_hours')->nullable();
            $table->string('service_category')->nullable();

            $table->string('service_location')->nullable();
            $table->string('service_location_area')->nullable();
            $table->string('service_city')->nullable();

            $table->string('caregiver_gender')->nullable();

            // Service Details
            $table->text('service_details')->nullable();
            $table->string('expected_fee_range')->nullable();
            $table->json('additional_facilities')->nullable();

            // Status
            $table->string('status')->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_service_requirements');
    }
};
