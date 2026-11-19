<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Patient extends Model
{
    protected $table = 'patients';
    protected $appends = [
        'photoPreviewUrl',

    ];
    public static $snakeAttributes = false;

    protected $fillable = [
        // Basic Information
        'patient_code',
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

        // Current Service Requirement
        'current_service_requirement_id',

        // Documents
        'photo',
        'id_proof',
        'medical_reports',
        'consent_form',
        'privacy_document',

        // Status
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'age' => 'integer',
    ];

    /**
     * Patient has multiple service requirements.
     */
    public function serviceRequirements(): HasMany
    {
        return $this->hasMany(
            PatientServiceRequirement::class,
            'patient_id'
        );
    }

    /**
     * Get currently selected service requirement.
     */
    public function currentServiceRequirement(): BelongsTo
    {
        return $this->belongsTo(
            PatientServiceRequirement::class,
            'current_service_requirement_id'
        );
    }

    /**
     * Patient has multiple caregiver assignment records.
     */
    public function caregiverAssignments(): HasMany
    {
        return $this->hasMany(
            PatientCaregiver::class,
            'patient_id'
        );
    }

    /**
     * Get caregivers assigned to this patient.
     * Caregiver records are stored in enrolments table.
     */
    public function caregivers(): BelongsToMany
    {
        return $this->belongsToMany(
            Enrolment::class,
            'patient_caregivers',
            'patient_id',
            'caregiver_id'
        )
            ->withPivot([
                'id',
                'service_requirement_id',
                'assigned_date',
                'start_date',
                'end_date',
                'status',
                'notes',
            ])
            ->withTimestamps();
    }

    /**
     * Get the full URL for the enrolment profile preview.
     */
    protected function photoPreviewUrl(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $path = !empty($attributes['photo'])
                    ? ltrim($attributes['photo'], '/')
                    : 'no-image.png';

                return rtrim(env('PUBLIC_URL'), '/') . '/' . $path;
            },
        );
    }
}
