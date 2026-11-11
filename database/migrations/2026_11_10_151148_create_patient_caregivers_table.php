<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_caregivers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')
                ->constrained('patients')
                ->cascadeOnDelete();

            $table->foreignId('caregiver_id')
                ->constrained('enrolments')
                ->cascadeOnDelete();

            $table->foreignId('service_requirement_id')
                ->nullable()
                ->constrained('patient_service_requirements')
                ->nullOnDelete();

            $table->date('assigned_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('status')->default('active');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['patient_id', 'status']);
            $table->index(['caregiver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_caregivers');
    }
};
