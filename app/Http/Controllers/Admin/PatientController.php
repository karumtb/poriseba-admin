<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientServiceRequirement;
use App\Models\Enrolment;
use App\Models\PatientCaregiver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

            // Preferred caregiver preferences (optional)
            'preferred_language' => ['nullable', 'string', 'max:100'],
            'preferred_religion' => ['nullable', 'string', 'max:100'],

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
            'preferred_religion',
            'preferred_language'
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

                // Enrolment table
                'caregiverAssignments.caregiver:id,first_name,last_name',
            ]);

        // Search
        if ($request->filled('search')) {
            $search = trim($request->input('search'));

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

            // Format caregiver name
            $patient->caregiverAssignments->each(function ($assignment) {

                if ($assignment->caregiver) {

                    $caregiver = $assignment->caregiver;

                    $caregiver->setAttribute(
                        'name',
                        trim(
                            ($caregiver->first_name ?? '') . ' ' .
                                ($caregiver->last_name ?? '')
                        )
                    );
                }
            });

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

                // Caregiver assignments with concatenated caregiver name
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
    /**
     * Available Caregiver .
     */




    public function available_caregiver(Request $request)
    {
        $user = Auth::user();

        /*
    |--------------------------------------------------------------------------
    | Validate Patient Code
    |--------------------------------------------------------------------------
    */


        $validator = Validator::make($request->all(), [
            'patient_code' => [
                'required',
                'string',
                'exists:patients,patient_code',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
    |--------------------------------------------------------------------------
    | Find Patient & Current Service Requirement
    |--------------------------------------------------------------------------
    */

        $patient = Patient::with('currentServiceRequirement')
            ->where('patient_code', $request->patient_code)
            ->first();

        if (!$patient) {
            return response()->json([
                'status' => false,
                'message' => 'Patient not found',
            ], 404);
        }

        $requirement = $patient->currentServiceRequirement;

        /*
    |--------------------------------------------------------------------------
    | Caregiver Query
    |--------------------------------------------------------------------------
    */

        $query = Enrolment::query()
            ->with(['vendor', 'designation'])
            ->select([
                'id',
                'first_name',
                'last_name',
                'caregiver_code',
                'phone',
                'status',
                'profile_photo',
                'designation',
                'is_engaged',
                'highest_qualification',
                'address',
                'area_id',
                'gender',
                'religion_id',
                'language_id',
                'vendor_id',
            ])
            ->selectRaw("CONCAT(first_name, ' ', last_name) as name");

        // Only active caregivers
        $query->where('status', 'A');
        $query->where('is_engaged', 0);
        /*
    |--------------------------------------------------------------------------
    | Vendor Restriction
    |--------------------------------------------------------------------------
    */

        if ($user->role === 'vendor') {
            $query->where('vendor_id', $user->id);
        }

        /*
    |--------------------------------------------------------------------------
    | Patient Preference Matching
    |--------------------------------------------------------------------------
    */

        if ($requirement) {

            // 1. Preferred Service Area
            if (!empty($requirement->service_location_area)) {
                $query->where(
                    'area_id',
                    $requirement->service_location_area
                );
            }

            // 2. Preferred Caregiver Gender
            if (!empty($requirement->caregiver_gender)) {
                $query->where(
                    'gender',
                    $requirement->caregiver_gender
                );
            }

            // 3. Preferred Religion
            if (!empty($requirement->preferred_religion)) {
                $query->where(
                    'religion_id',
                    $requirement->preferred_religion
                );
            }

            // 4. Preferred Language
            if (!empty($requirement->preferred_language)) {
                $query->where(
                    'language_id',
                    $requirement->preferred_language
                );
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

        $perPage = $request->input('per_page', 10);

        /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

        $search = trim($request->input('keyword', ''));
        $status = trim($request->input('status', ''));

        if (!empty($search)) {

            $query->where(function ($q) use ($search) {

                $q->whereRaw(
                    "CONCAT(first_name, ' ', last_name) LIKE ?",
                    ["%{$search}%"]
                )
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('placement_location', 'like', "%{$search}%")
                    ->orWhere('caregiver_code', 'like', "%{$search}%");
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Additional Filters
    |--------------------------------------------------------------------------
    */

        if ($request->filled('designation')) {
            $query->where('designation', $request->designation);
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        /*
    |--------------------------------------------------------------------------
    | Get Caregivers
    |--------------------------------------------------------------------------
    */

        $enrolments = $query
            ->latest()
            ->paginate($perPage);

        /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

        return response()->json([
            'status' => true,
            'message' => 'Available caregivers fetched successfully',
            'patient_code' => $patient->patient_code,
            'data' => $enrolments,
        ]);
    }

    public function hireCaregiver(Request $request)
    {
        $validated = $request->validate([
            'patient_code' => 'required|string|exists:patients,patient_code',
            'caregiver_id' => 'required|integer|exists:enrolments,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validator = Validator::make($request->all(), [
            'patient_code' => [
                'required',
                'string',
                'exists:patients,patient_code',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $assignment = DB::transaction(function () use ($validated) {

                // -----------------------------------------
                // 1. Fetch Patient
                // -----------------------------------------

                $patient = Patient::where(
                    'patient_code',
                    $validated['patient_code']
                )->lockForUpdate()->firstOrFail();


                // -----------------------------------------
                // 2. Fetch Caregiver from Enrolments
                // -----------------------------------------

                $caregiver = Enrolment::where(
                    'id',
                    $validated['caregiver_id']
                )->lockForUpdate()->firstOrFail();


                // -----------------------------------------
                // 3. Check Caregiver Status
                // -----------------------------------------

                if ($caregiver->status !== 'A') {
                    throw ValidationException::withMessages([
                        'caregiver_id' => 'This caregiver is inactive.',
                    ]);
                }


                // -----------------------------------------
                // 4. Check Caregiver Availability
                // -----------------------------------------

                if ((int) $caregiver->is_engaged === 1) {
                    throw ValidationException::withMessages([
                        'caregiver_id' => 'This caregiver is already hired.',
                    ]);
                }


                // -----------------------------------------
                // 5. Get Patient Current Service Requirement
                // -----------------------------------------

                $requirementId = $patient->current_service_requirement_id;

                if (!$requirementId) {
                    throw ValidationException::withMessages([
                        'service_requirement_id' =>
                        'No service requirement found for this patient.',
                    ]);
                }

                $requirement = PatientServiceRequirement::where('id', $requirementId)
                    ->where('patient_id', $patient->id)
                    ->lockForUpdate()
                    ->first();

                if (!$requirement) {
                    throw ValidationException::withMessages([
                        'service_requirement_id' =>
                        'Patient service requirement not found.',
                    ]);
                }


                // -----------------------------------------
                // 6. Calculate Start Date and End Date
                // -----------------------------------------

                if (!$requirement->care_start_date) {
                    throw ValidationException::withMessages([
                        'care_start_date' => 'Care start date is missing.',
                    ]);
                }

                $startDate = Carbon::parse($requirement->care_start_date);

                $endDate = null;

                $duration = (int) $requirement->duration;

                $durationUnit = strtolower(
                    trim($requirement->duration_unit ?? '')
                );

                if ($duration > 0 && $durationUnit !== '') {

                    $endDate = $startDate->copy();

                    switch ($durationUnit) {

                        case 'day':
                        case 'days':
                            $endDate->addDays($duration);
                            break;

                        case 'week':
                        case 'weeks':
                            $endDate->addWeeks($duration);
                            break;

                        case 'month':
                        case 'months':
                            $endDate->addMonthsNoOverflow($duration);
                            break;

                        case 'year':
                        case 'years':
                            $endDate->addYearsNoOverflow($duration);
                            break;

                        default:
                            throw ValidationException::withMessages([
                                'duration_unit' => 'Invalid service duration unit.',
                            ]);
                    }
                }


                // -----------------------------------------
                // 7. Check Existing Active Assignment
                // -----------------------------------------

                $existingAssignment = PatientCaregiver::where(
                    'patient_id',
                    $patient->id
                )
                    ->where('caregiver_id', $caregiver->id)
                    ->where('status', 'A')
                    ->exists();

                if ($existingAssignment) {
                    throw ValidationException::withMessages([
                        'caregiver_id' =>
                        'This caregiver is already assigned to this patient.',
                    ]);
                }


                // -----------------------------------------
                // 8. Create Patient Caregiver Assignment
                // -----------------------------------------

                $assignment = PatientCaregiver::create([
                    'patient_id' => $patient->id,

                    'caregiver_id' => $caregiver->id,

                    'service_requirement_id' => $requirement->id,

                    'assigned_date' => now()->toDateString(),

                    'start_date' => $startDate->toDateString(),

                    'end_date' => $endDate
                        ? $endDate->toDateString()
                        : null,

                    'status' => 'A',

                    'notes' => $validated['notes'] ?? null,
                ]);


                // -----------------------------------------
                // 9. Update Caregiver Engagement Status
                // -----------------------------------------

                $caregiver->update([
                    'is_engaged' => 1,
                ]);


                return $assignment;
            });


            // -----------------------------------------
            // 10. Load Assignment Relationships
            // -----------------------------------------

            $assignment->load([
                'patient',
                'caregiver',
                'serviceRequirement',
            ]);


            return response()->json([
                'status' => true,
                'message' => 'Caregiver hired successfully.',
                'data' => $assignment,
            ], 200);
        } catch (ValidationException $e) {

            throw $e;
        } catch (\Throwable $e) {

            Log::error('Hire Caregiver Error', [
                'error' => $e->getMessage(),
                'patient_code' => $validated['patient_code'] ?? null,
                'caregiver_id' => $validated['caregiver_id'] ?? null,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Unable to hire caregiver. Please try again.',
            ], 500);
        }
    }

    public function assignmentHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_code' => 'required|string|exists:patients,patient_code',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        try {

            // Find patient
            $patient = Patient::where(
                'patient_code',
                $validated['patient_code']
            )->first();

            if (!$patient) {
                return response()->json([
                    'status' => false,
                    'message' => 'Patient not found.',
                ], 404);
            }

            // Pagination
            $perPage = $validated['per_page'] ?? 10;
            $page = $validated['page'] ?? 1;

            // Fetch assignment history
            $assignments = PatientCaregiver::with([
                'patient',
                'caregiver.designation',
                'serviceRequirement',
            ])
                ->where('patient_id', $patient->id)
                ->orderByDesc('assigned_date')
                ->orderByDesc('id')
                ->paginate($perPage, ['*'], 'page', $page);

            // Status mapping
            $statusMap = [
                'A' => 'Active',
                'C' => 'Completed',
                'R' => 'Reassigned',
                'I' => 'Inactive',
            ];

            // Format assignment history
            $history = $assignments->getCollection()->map(function ($item) use ($statusMap) {

                $caregiver = $item->caregiver;
                $requirement = $item->serviceRequirement;

                // Assignment year
                $assignmentDate = $item->assigned_date ?? $item->created_at;

                $year = $assignmentDate
                    ? $assignmentDate->format('Y')
                    : date('Y');

                // Generate assignment code
                $assignmentCode = 'ASG-' . $year . '-' .
                    str_pad($item->id, 3, '0', STR_PAD_LEFT);

                // Caregiver name
                $caregiverName = '-';

                if ($caregiver) {
                    $caregiverName = $caregiver->name
                        ?? trim(
                            ($caregiver->first_name ?? '') . ' ' .
                                ($caregiver->last_name ?? '')
                        );

                    $caregiverName = $caregiverName ?: '-';
                }

                return [
                    'id' => $item->id,

                    'assignment_id' => $assignmentCode,

                    'patient_id' => $item->patient_id,

                    'patient_name' => $item->patient?->patient_name ?? '-',

                    'patient_code' => $item->patient?->patient_code ?? '-',

                    'caregiver_id' => $item->caregiver_id,

                    'caregiver_name' => $caregiverName,

                    'caregiver_code' => $caregiver?->caregiver_code ?? '-',

                    'role' => $caregiver?->designation?->name ?? '-',

                    'from' => $item->start_date
                        ? $item->start_date->format('d M Y')
                        : null,

                    'to' => $item->end_date
                        ? $item->end_date->format('d M Y')
                        : null,

                    'duty_hours' => $requirement?->duty_hours ?? '-',

                    'fee' => $item->fee_per_hour
                        ?? $requirement?->expected_fee_range
                        ?? '-',

                    'total_amount' => $item->total_amount ?? null,

                    'status' => $statusMap[$item->status]
                        ?? ucfirst((string) $item->status),

                    'remarks' => $item->notes ?? '-',

                    'assigned_date' => $item->assigned_date
                        ? $item->assigned_date->format('d M Y')
                        : null,
                ];
            });

            $assignments->setCollection($history);

            return response()->json([
                'status' => true,
                'message' => 'Assignment history fetched successfully.',

                'data' => [
                    'patient' => [
                        'id' => $patient->id,
                        'patient_code' => $patient->patient_code,
                        'patient_name' => $patient->patient_name,
                    ],

                    'assignments' => $assignments,
                ],

            ], 200);
        } catch (\Throwable $e) {

            Log::error('Assignment History Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'patient_code' => $validated['patient_code'] ?? null,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Unable to fetch assignment history.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
