<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        // Basic Information
        'patient_name',
        'date_of_birth',
        'age',
        'gender',

        // Contact & Address
        'primary_contact_name',
        'relationship',
        'phone_number',
        'alternate_number',
        'location_area',
        'address',
        'city',
        'state',
        'pin_code',

        // Emergency Contact
        'emergency_contact_name',
        'emergency_relationship',
        'emergency_phone',

        // Medical Information
        'primary_diagnosis',
        'secondary_diagnosis',
        'health_status',
        'allergies',
        'chronic_conditions',
        'medical_history',
        'current_medications',
        'special_care_needs',
        'dietary_preferences',

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

        // Documents
        'photo',
        'id_proof',
        'medical_reports',
        'consent_form',
        'privacy_document',

        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'care_start_date' => 'date',
            'additional_facilities' => 'array',
            'age' => 'integer',
            'duration' => 'integer',
        ];
    }
}
