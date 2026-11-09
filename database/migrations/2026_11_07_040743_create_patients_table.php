
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('patient_code')->unique()->nullable();
            $table->string('patient_name');
            $table->date('date_of_birth');
            $table->unsignedInteger('age')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other']);

            // Contact & Address
            $table->string('primary_contact_name');
            $table->string('relationship');
            $table->string('phone_number', 20);
            $table->string('alternate_number', 20)->nullable();
            $table->string('location_area')->nullable();
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pin_code', 15)->nullable();

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_relationship')->nullable();
            $table->string('emergency_phone', 20)->nullable();

            // Medical / Care Information
            $table->string('primary_diagnosis');
            $table->string('secondary_diagnosis')->nullable();
            $table->string('health_status');
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->longText('medical_history')->nullable();
            $table->longText('current_medications')->nullable();
            $table->longText('special_care_needs')->nullable();
            $table->text('dietary_preferences')->nullable();

            // Service Requirement
            $table->string('care_type');
            $table->date('care_start_date');
            $table->unsignedInteger('duration');
            $table->string('duration_unit');
            $table->string('duty_hours');
            $table->string('service_category')->nullable();
            $table->string('service_location');
            $table->string('service_location_area')->nullable();
            $table->string('service_city')->nullable();
            $table->string('caregiver_gender')->nullable();

            // Service Details & Instructions
            $table->longText('service_details')->nullable();
            $table->string('expected_fee_range')->nullable();
            $table->json('additional_facilities')->nullable();

            // Consent & Documents
            // Store file paths only. Upload handled separately.
            $table->string('photo')->nullable();
            $table->string('id_proof')->nullable();
            $table->text('medical_reports')->nullable();
            $table->string('consent_form')->nullable();
            $table->string('privacy_document')->nullable();

            // Patient onboarding status
            $table->string('status')->default('draft');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
