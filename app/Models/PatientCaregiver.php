<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCaregiver extends Model
{
    protected $table = 'patient_caregivers';

    protected $fillable = [
        'patient_id',
        'caregiver_id',
        'service_requirement_id',
        'assigned_date',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Assignment belongs to a patient.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Caregiver is stored in enrolments table.
     */
    public function caregiver(): BelongsTo
    {
        return $this->belongsTo(
            Enrolment::class,
            'caregiver_id'
        );
    }

    /**
     * Assignment belongs to a service requirement.
     */
    public function serviceRequirement(): BelongsTo
    {
        return $this->belongsTo(
            PatientServiceRequirement::class,
            'service_requirement_id'
        );
    }
}
