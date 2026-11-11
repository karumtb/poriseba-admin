<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientServiceRequirement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    /**
     * Patient validation rules.
     */
    private function validationRules(): array
    {
        $documentRules = [
            'nullable',
            'file',
            'mimes:jpg,jpeg,png,webp,pdf',
            'max:2048'
        ];

        return [
            // Basic information
            'patient_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],

            // Contact and address
            'primary_contact_name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:20'],
            'alternate_number' => ['nullable', 'string', 'max:20'],
            'location_area' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pin_code' => ['nullable', 'string', 'max:15'],

            // Emergency contact
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],

            // Medical information
            'primary_diagnosis' => ['required', 'string', 'max:255'],
            'secondary_diagnosis' => ['nullable', 'string', 'max:255'],
            'health_status' => ['required', 'string', 'max:100'],
            'allergies' => ['nullable', 'string'],
            'chronic_conditions' => ['nullable', 'string'],
            'medical_history' => ['nullable', 'string'],
            'current_medications' => ['nullable', 'string'],
            'special_care_needs' => ['nullable', 'string'],
            'dietary_preferences' => ['nullable', 'string'],

            // Currently selected service requirement
            'current_service_requirement_id' => [
                'nullable',
                'integer',
                'exists:patient_service_requirements,id'
            ],

            // Service requirement
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

            // Service details
            'service_details' => ['nullable', 'string'],
            'expected_fee_range' => ['nullable', 'string', 'max:100'],
            'additional_facilities' => ['nullable', 'array'],
            'additional_facilities.*' => ['string', 'max:255'],

            // Patient photo
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024'
            ],

            // Documents
            'id_proof' => $documentRules,
            'medical_reports' => $documentRules,
            'consent_form' => $documentRules,
            'privacy_document' => $documentRules,
        ];
    }

    /**
     * Service requirement fields.
     */
    private function serviceRequirementFields(): array
    {
        return [
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
        ];
    }

    /**
     * Store or update patient.
     */
    public function store(Request $request)
    {
        $id = $request->input('id');

        // Find patient.
        $patient = $id
            ? Patient::find($id)
            : new Patient();

        if ($id && !$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found.',
            ], 404);
        }

        // Validate request.
        $validated = $request->validate($this->validationRules());

        $fileFields = [
            'photo',
            'id_proof',
            'medical_reports',
            'consent_form',
            'privacy_document',
        ];

        $serviceFields = $this->serviceRequirementFields();

        /*
         * Separate patient data and service requirement data.
         */

        $serviceData = [];

        foreach ($serviceFields as $field) {
            if (array_key_exists($field, $validated)) {
                $serviceData[$field] = $validated[$field];
            }
        }

        $patientData = $validated;

        foreach ($serviceFields as $field) {
            unset($patientData[$field]);
        }

        // Remove requirement selection from patient fill data.
        unset($patientData['current_service_requirement_id']);

        $oldPaths = [];
        $newPaths = [];

        $uploadDirectory = public_path('uploads/patients');

        foreach ($fileFields as $field) {
            $oldPaths[$field] = $patient->{$field};
        }

        try {

            /*
             * Create upload directory.
             */

            if (!File::isDirectory($uploadDirectory)) {
                File::makeDirectory(
                    $uploadDirectory,
                    0755,
                    true,
                    true
                );
            }

            /*
             * Upload patient documents.
             */

            foreach ($fileFields as $field) {

                if (!$request->hasFile($field)) {

                    // Retain existing file.
                    if ($id) {
                        $patientData[$field] = $oldPaths[$field];
                    }

                    continue;
                }

                $uploadedFile = $request->file($field);

                $extension = strtolower(
                    $uploadedFile->guessExtension()
                        ?: $uploadedFile->getClientOriginalExtension()
                );

                $filename = Str::uuid()->toString() . '.' . $extension;

                $uploadedFile->move(
                    $uploadDirectory,
                    $filename
                );

                $relativePath = 'uploads/patients/' . $filename;

                $patientData[$field] = $relativePath;

                $newPaths[$field] = $relativePath;
            }

            /*
             * Calculate patient age.
             */

            $patientData['age'] = Carbon::parse(
                $patientData['date_of_birth']
            )->age;

            /*
             * Get selected service requirement ID.
             */

            $selectionProvided = array_key_exists(
                'current_service_requirement_id',
                $validated
            );

            $selectedRequirementId =
                $validated['current_service_requirement_id'] ?? null;

            /*
             * Check whether service requirement data is available.
             */

            $hasServiceData = collect($serviceData)->contains(
                fn($value) =>
                $value !== null &&
                    $value !== '' &&
                    $value !== []
            );

            /*
             * Save patient and service requirement.
             */

            DB::transaction(function () use (
                $patient,
                $patientData,
                $id,
                $selectionProvided,
                $selectedRequirementId,
                $serviceData,
                $hasServiceData
            ) {

                // 1. Save patient information.

                $patient->fill($patientData);

                if (!$id) {
                    $patient->status = 'draft';
                }

                $patient->save();

                // Generate patient code.
                if (!$id) {

                    $patient->patient_code = 'PT-' .
                        now()->format('Y') . '-' .
                        str_pad(
                            $patient->id,
                            6,
                            '0',
                            STR_PAD_LEFT
                        );

                    $patient->save();
                }

                /*
                 * 2. Determine selected service requirement.
                 *
                 * If current_service_requirement_id is not sent,
                 * retain the patient's existing selection.
                 */

                $requirementId = $selectionProvided
                    ? $selectedRequirementId
                    : $patient->current_service_requirement_id;

                if ($requirementId) {

                    /*
                     * Update existing requirement.
                     * Ensure requirement belongs to this patient.
                     */

                    $requirement = PatientServiceRequirement::where(
                        'patient_id',
                        $patient->id
                    )->find($requirementId);

                    if (!$requirement) {
                        throw ValidationException::withMessages([
                            'current_service_requirement_id' => [
                                'Selected service requirement does not belong to this patient.'
                            ],
                        ]);
                    }

                    // Update service requirement fields.
                    if ($hasServiceData) {
                        $requirement->fill($serviceData);
                        $requirement->save();
                    }

                    $patient->current_service_requirement_id =
                        $requirement->id;
                } elseif ($hasServiceData) {

                    /*
                     * 3. Create new service requirement.
                     */

                    $requirement = $patient->serviceRequirements()->create(
                        array_merge($serviceData, [
                            'status' => 'active',
                        ])
                    );

                    // Set newly created requirement as current.
                    $patient->current_service_requirement_id =
                        $requirement->id;
                } elseif ($selectionProvided) {

                    // Explicitly clear current requirement.
                    $patient->current_service_requirement_id = null;
                }

                // Save current service requirement ID.
                $patient->save();
            });

            /*
             * 4. Delete old files after transaction succeeds.
             */

            foreach ($newPaths as $field => $newPath) {

                $oldPath = $oldPaths[$field];

                if ($oldPath && $oldPath !== $newPath) {
                    $this->deletePatientUpload($oldPath);
                }
            }

            /*
             * 5. Get updated patient details.
             */

            $freshPatient = Patient::with([
                'currentServiceRequirement',
                'serviceRequirements',
            ])->findOrFail($patient->id);

            /*
             * 6. Generate file URLs.
             */

            $fileUrls = [];

            foreach ($fileFields as $field) {

                $fileUrls[$field . '_url'] =
                    $freshPatient->{$field}
                    ? asset($freshPatient->{$field})
                    : null;
            }

            return response()->json([

                'success' => true,

                'message' => $id
                    ? 'Patient updated successfully.'
                    : 'Patient created successfully.',

                'data' => $freshPatient,

                'current_service_requirement' =>
                $freshPatient->currentServiceRequirement,

                'files' => $fileUrls,

            ], $id ? 200 : 201);
        } catch (ValidationException $e) {

            // Remove newly uploaded files if validation fails.
            foreach ($newPaths as $newPath) {
                $this->deletePatientUpload($newPath);
            }

            throw $e;
        } catch (Throwable $e) {

            // Clean up uploaded files if saving fails.
            foreach ($newPaths as $newPath) {
                $this->deletePatientUpload($newPath);
            }

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to save patient. Please try again.',
            ], 500);
        }
    }

    /**
     * Delete only files managed by this controller.
     */
    private function deletePatientUpload(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $relativePath = ltrim(
            str_replace('\\', '/', $relativePath),
            '/'
        );

        if (strpos($relativePath, 'uploads/patients/') !== 0) {
            return;
        }

        $absolutePath = public_path($relativePath);

        if (
            File::exists($absolutePath) &&
            File::isFile($absolutePath)
        ) {
            File::delete($absolutePath);
        }
    }

    /**
     * Patient listing.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $query = Patient::query()
            ->select([
                'id',
                'patient_code',
                'patient_name',
                'age',
                'gender',
                'phone_number',
                'alternate_number',
                'city',
                'state',
                'pin_code',
                'photo',
                'current_service_requirement_id',
                'status',
                'created_at',
            ])
            ->with([
                'currentServiceRequirement:id,patient_id,care_type,service_category,duty_hours',
                'serviceRequirements:id,patient_id,care_type,service_category',
                'caregiverAssignments:id,patient_id,caregiver_id,status',
                'caregiverAssignments.caregiver:id,name',
            ]);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                    ->orWhere('patient_code', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $patients = $query
            ->latest()
            ->paginate($perPage);

        // Format response
        $patients->getCollection()->transform(function ($patient) {

            return [
                'id' => $patient->id,
                'patient_code' => $patient->patient_code,
                'patient_name' => $patient->patient_name,
                'age' => $patient->age,
                'gender' => $patient->gender,
                'phone_number' => $patient->phone_number,
                'alternate_number' => $patient->alternate_number,
                'city' => $patient->city,
                'state' => $patient->state,
                'pin_code' => $patient->pin_code,

                // Patient photo URL
                'photo_url' => $patient->photo
                    ? asset($patient->photo)
                    : null,

                'status' => $patient->status,
                'created_at' => $patient->created_at,

                // Current service requirement
                'current_service_requirement' => $patient->currentServiceRequirement,

                // All service requirements
                'service_requirements' => $patient->serviceRequirements,

                // Caregiver assignments
                'caregiver_assignments' => $patient->caregiverAssignments,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Patients fetched successfully.',
            'data' => $patients,
        ]);
    }



    /**
     * View patient details for View/Edit.
     */
    public function view(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => ['required', 'integer', 'min:1', 'exists:patients,id'],
            ],
            [
                'id.required' => 'Patient ID is required.',
                'id.integer'  => 'Patient ID must be a valid integer.',
                'id.min'      => 'Patient ID must be greater than zero.',
                'id.exists'   => 'Patient not found.',
            ]
        );

        // Check validation errors
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Get validated data
        $validated = $validator->validated();

        // Fetch patient
        $patient = Patient::with([
            'currentServiceRequirement',
            'serviceRequirements',
            'caregiverAssignments',
        ])->find($validated['id']);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found.',
            ], 404);
        }

        $fileFields = [
            'photo',
            'id_proof',
            'medical_reports',
            'consent_form',
            'privacy_document',
        ];

        $files = [];

        foreach ($fileFields as $field) {
            $files[$field] = $patient->{$field}
                ? env('PUBLIC_URL') . $patient->{$field}
                : null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Patient details fetched successfully.',

            'data' => [
                'patient' => $patient,
                'current_service_requirement' => $patient->currentServiceRequirement,
                'service_requirements' => $patient->serviceRequirements,
                'files' => $files,
            ],
        ]);
    }
}
