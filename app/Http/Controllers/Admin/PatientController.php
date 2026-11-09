<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PatientController extends Controller
{
    /**
     * Patient Validation Rules
     */
    private function validationRules(): array
    {
        return [
            // Basic Information
            'patient_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],

            // Contact & Address
            'primary_contact_name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:20'],
            'alternate_number' => ['nullable', 'string', 'max:20'],
            'location_area' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pin_code' => ['nullable', 'string', 'max:15'],

            // Emergency Contact
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],

            // Medical Information
            'primary_diagnosis' => ['required', 'string', 'max:255'],
            'secondary_diagnosis' => ['nullable', 'string', 'max:255'],
            'health_status' => ['required', 'string', 'max:100'],
            'allergies' => ['nullable', 'string'],
            'chronic_conditions' => ['nullable', 'string'],
            'medical_history' => ['nullable', 'string'],
            'current_medications' => ['nullable', 'string'],
            'special_care_needs' => ['nullable', 'string'],
            'dietary_preferences' => ['nullable', 'string'],

            // Service Requirement
            'care_type' => ['required', 'string', 'max:100'],
            'care_start_date' => ['required', 'date'],
            'duration' => ['required', 'integer', 'min:1'],
            'duration_unit' => [
                'required',
                Rule::in(['Months', 'Weeks', 'Days'])
            ],
            'duty_hours' => ['required', 'string', 'max:50'],
            'service_category' => ['nullable', 'string', 'max:150'],
            'service_location' => ['required', 'string', 'max:150'],
            'service_location_area' => ['nullable', 'string', 'max:255'],
            'service_city' => ['nullable', 'string', 'max:100'],
            'caregiver_gender' => [
                'nullable',
                Rule::in(['Any', 'Male', 'Female'])
            ],

            // Service Details
            'service_details' => ['nullable', 'string'],
            'expected_fee_range' => ['nullable', 'string', 'max:100'],
            'additional_facilities' => ['nullable', 'array'],
            'additional_facilities.*' => ['string', 'max:255'],

            // Document Paths
            'photo' => ['nullable', 'string', 'max:2048'],
            'id_proof' => ['nullable', 'string', 'max:2048'],
            'medical_reports' => ['nullable', 'string'],
            'consent_form' => ['nullable', 'string', 'max:2048'],
            'privacy_document' => ['nullable', 'string', 'max:2048'],
        ];
    }

    /**
     * Add / Edit Patient
     *
     * ID passed     = Update
     * ID not passed = Insert
     */
    public function save(Request $request)
    {
        $id = $request->input('id');

        // Find existing patient or create new instance
        $patient = $id
            ? Patient::find($id)
            : new Patient();

        if ($id && !$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found.',
            ], 404);
        }

        // Validate request
        $validated = $request->validate($this->validationRules());

        // Calculate age
        $validated['age'] = Carbon::parse(
            $validated['date_of_birth']
        )->age;

        // Fill patient data
        $patient->fill($validated);

        // Default status for new patient
        if (!$id) {
            $patient->status = 'draft';
        }

        // Save patient
        $patient->save();

        // Generate patient code after insertion
        if (!$id) {
            $patient->patient_code = 'PT-' .
                now()->format('Y') . '-' .
                str_pad($patient->id, 6, '0', STR_PAD_LEFT);

            $patient->save();
        }

        return response()->json([
            'success' => true,
            'message' => $id
                ? 'Patient updated successfully.'
                : 'Patient created successfully.',
            'data' => $patient->fresh(),
        ], $id ? 200 : 201);
    }
    /**
     * List enrolments
     */
    public function index(Request $request)
    {

        $query = Patient::query();
        $perPage = $request->input('per_page', 10);



        $patients = $query->latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $patients,
        ]);
    }
}
