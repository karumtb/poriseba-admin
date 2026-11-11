<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientServiceRequirement extends Model
{
    protected $table = 'patient_service_requirements';

    protected $fillable = [
        'patient_id',

        // Service Requirement
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

        // Service Details
        'service_details',
        'expected_fee_range',
        'additional_facilities',

        'status',
    ];

    protected $casts = [
        'care_start_date' => 'date',
        'additional_facilities' => 'array',
        'duration' => 'integer',
    ];

    /**
     * Requirement belongs to a patient.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Requirement can have multiple caregiver assignments.
     */
    public function caregiverAssignments(): HasMany
    {
        return $this->hasMany(
            PatientCaregiver::class,
            'service_requirement_id'
        );
    }
}
