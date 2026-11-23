
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrolments', function (Blueprint $table) {

            // Basic Information
            $table->string('middle_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('alternate_phone', 20)->nullable();
            $table->string('district')->nullable();

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number', 20)->nullable();
            $table->string('emergency_contact_relationship')->nullable();

            // Professional Information
            $table->text('professional_summary')->nullable();
            $table->string('primary_role')->nullable();

            // Career & Availability
            $table->string('career_status')->nullable();
            $table->string('availability_status')->nullable();
            $table->string('placement_status')->nullable();

            // Multiple preferred duty types and locations
            $table->json('preferred_duty_types')->nullable();
            $table->json('preferred_locations')->nullable();

            // Verification Information
            $table->string('verification_status')
                ->default('Pending');

            $table->timestamp('verified_at')->nullable();
            $table->text('verification_remark')->nullable();

            // Registry Profile Type (Optional)
            $table->string('profile_type')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('enrolments', function (Blueprint $table) {

            $table->dropColumn([
                'middle_name',
                'date_of_birth',
                'alternate_phone',
                'district',

                'emergency_contact_name',
                'emergency_contact_number',
                'emergency_contact_relationship',

                'professional_summary',
                'primary_role',

                'career_status',
                'availability_status',
                'placement_status',

                'preferred_duty_types',
                'preferred_locations',

                'verification_status',
                'verified_at',
                'verification_remark',

                'profile_type',
            ]);
        });
    }
};
