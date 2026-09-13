<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Enrolment;
use App\Models\PlacementApplication;
use App\Models\ViewPlacementApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EnrolmentController extends Controller
{
     /**
     * List enrolments
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Enrolment::query()
                ->with(['vendor','designation'])
                ->select(['id','first_name','last_name','vendor_id','phone','status','profile_photo','designation','hospital_id','highest_qualification','address'])
                ->selectRaw("CONCAT(first_name, ' ', last_name) as name");

            // Vendor can only see his own enrolments
            if ($user->role === 'vendor') {
                $query->where('vendor_id', $user->id);
            }
            if ($user->role === 'hospital') {
              $query->whereNull('hospital_id');
            }
            if($user->role!='admin')
            {
             $query->where('status', 'A');
            }
            $perPage = $request->input('per_page', 10);

            $search = trim($request->input('keyword', ''));
            $status = trim($request->input('status', ''));


            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw(
                        "CONCAT(first_name, ' ', last_name) LIKE ?",
                        ["%{$search}%"]
                    )
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
                });
            }
            if(!empty($request->designation))
            {
                $query->where('designation', $request->designation);
            }
            if(!empty($status))
            {
                $query->where('status',$status);
            }

            $enrolments = $query->latest()->paginate($perPage);

            return response()->json([
                'status' => true,
                'data' => $enrolments,
            ]);
    }

        /**
     * List Placement Application
     */
    public function placement_request(Request $request)
    {
        try {
         $query = ViewPlacementApplication::query();
        // Search
        if ($request->filled('keyword')) {
            $search = $request->keyword;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('phone', 'LIKE', "%{$search}%")
                ->orWhere('hospital', 'LIKE', "%{$search}%")
                ->orWhere('vendor', 'LIKE', "%{$search}%");
            });
        }

        // Designation filter
        if ($request->filled('designation')) {
            $query->where('designation_id', $request->designation);
        }

        $applications = $query
        ->orderByDesc('id')
        ->paginate($request->get('per_page', 10));

        return response()->json([
            'status' => true,
            'data' => $applications,
        ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
            'success' => false,
            'data' => $th->getMessage(),
        ]);
        }

    }


    /**
     * Create / Update enrolment
     */
    public function save(Request $request)
    {
        $isEdit = !empty($request->id);
        $id = $request->id;
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Find enrolment for edit
        |--------------------------------------------------------------------------
        */
        if ($isEdit) {

            $enrolment = Enrolment::find($request->id);

            if (!$enrolment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enrolment not found',
                ], 404);
            }

        } else {
            $enrolment = new Enrolment();
        }


        /*
        |--------------------------------------------------------------------------
        | Vendor
        |--------------------------------------------------------------------------
        */
        if ($user->role === 'vendor') {

            // Vendor can only use his own ID
            $vendorId = $user->id;

        } else {

            $request->validate([
                'vendor_id' => [
                    'required',
                    'exists:users,id',
                ],
            ]);

            $vendorId = $request->vendor_id;
        }


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
        $rules = [

            'first_name' => [
                'required',
                'string',
                'max:255',
            ],

             'last_name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('enrolments', 'email')->ignore($id),
            ],

            'phone' => [
                'required',
                'regex:/^[0-9]{10}$/',
                  Rule::unique('enrolments', 'phone')->ignore($id),
            ],
           'designation' => [
                'required',
                'integer',
                Rule::exists('designations', 'id'),
            ],

            'gender' => [
                'required',
                Rule::in(['M', 'F']),
            ],


            'address' => [
                'required',
                'string',
            ],
             'placement_start' => [
                'nullable',
                'date',
            ],
             'placement_end' => [
                'nullable',
                'date',
            ],

            'city' => [
                'required',
                'string',
                'max:255',
            ],

            'state' => [
                'required',
                'string',
                'max:255',
            ],

            'pin_code' => [
                'required',
                'regex:/^[0-9]{6}$/',
            ],

            'highest_qualification' => [
                'required',
                'string',
                'max:255',
            ],



            'experience' => [
                'nullable',
                'string',
                'max:255',
            ],

            'specialization' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | Files
        |--------------------------------------------------------------------------
        |
        | Create -> required
        | Edit   -> optional
        |--------------------------------------------------------------------------
        */

        $rules['profile_photo_file'] = [
            $isEdit ? 'nullable' : 'required',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
        ];

        $rules['adhar_pdf_file'] = [
            $isEdit ? 'nullable' : 'required',
            'file',
            'mimes:pdf',
            'max:5120',
        ];

        $rules['certificate_pdf_file'] = [
            $isEdit ? 'nullable' : 'required',
            'file',
            'mimes:pdf',
            'max:5120',
        ];


        $validated = $request->validate($rules);


        /*
        |--------------------------------------------------------------------------
        | Fill Enrolment
        |--------------------------------------------------------------------------
        */

        $data = $request->only([
            'first_name',
            'last_name',
            'email',
            'phone',
            'address',
            'city',
            'state',
            'pin_code',
            'highest_qualification',
            'designation',
            'experience',
            'specialization',
            'rating',
            'remark',
            'placement_location',
            'placement_start',
            'placement_end',
            'fb_reviews',
            'insta_reviews',
            'google_reviews',
            'gender'
        ]);

        // Always use the authenticated/validated vendor
        $data['vendor_id'] = $vendorId;

        $enrolment->fill($data);


            /*
        |--------------------------------------------------------------------------
        | Profile Photo
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('profile_photo_file')) {

            // Delete old file if editing
            if ($isEdit && $enrolment->profile_photo && file_exists(public_path($enrolment->profile_photo))) {
                unlink(public_path($enrolment->profile_photo));
            }

            // 1. Ensure target directory exists in public/
            $targetDir = public_path('enrolments/profile');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // 2. Upload file
            $file = $request->file('profile_photo_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move($targetDir, $filename);

            $enrolment->profile_photo = 'enrolments/profile/' . $filename;
        }


        /*
        |--------------------------------------------------------------------------
        | Aadhaar PDF
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('adhar_pdf_file')) {

            if ($isEdit && $enrolment->adhar_pdf && file_exists(public_path($enrolment->adhar_pdf))) {
                unlink(public_path($enrolment->adhar_pdf));
            }

            // 1. Ensure target directory exists in public/
            $targetDir = public_path('enrolments/adhar');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // 2. Upload file
            $file = $request->file('adhar_pdf_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move($targetDir, $filename);

            $enrolment->adhar_pdf = 'enrolments/adhar/' . $filename;
        }


        /*
        |--------------------------------------------------------------------------
        | Certificate PDF
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('certificate_pdf_file')) {

            if ($isEdit && $enrolment->certificate_pdf && file_exists(public_path($enrolment->certificate_pdf))) {
                unlink(public_path($enrolment->certificate_pdf));
            }

            // 1. Ensure target directory exists in public/
            $targetDir = public_path('enrolments/certificate');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // 2. Upload file
            $file = $request->file('certificate_pdf_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move($targetDir, $filename);

            $enrolment->certificate_pdf = 'enrolments/certificate/' . $filename;
        }



        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        $enrolment->save();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'status' => true,
            'message' => $isEdit
                ? 'Enrolment updated successfully'
                : 'Enrolment created successfully',
            'data' => $enrolment->load('vendor'),
        ]);
    }

    /**
     * View single enrolment
     */
    public function view(Request $request)
    {
        $request->validate([
            'id' => [
                'required',
                'exists:enrolments,id',
            ],
        ]);

        $user = Auth::user();

        $query = Enrolment::with(['vendor','designation'])

            ->where('id', $request->id);

        // Vendor can only access his own enrolment
        if ($user->role === 'vendor') {
            $query->where('vendor_id', $user->id);
        }

        $enrolment = $query->first();

        if (!$enrolment) {
            return response()->json([
                'success' => false,
                'message' => 'Enrolment not found',
            ], 404);
        }
        $enrolment->remark=!empty($enrolment->remark)?$enrolment->remark:'';
        $enrolment->fb_reviews=!empty($enrolment->fb_reviews)?$enrolment->fb_reviews:'';
        $enrolment->insta_reviews=!empty($enrolment->insta_reviews)?$enrolment->insta_reviews:'';
        $enrolment->google_reviews=!empty($enrolment->google_reviews)?$enrolment->google_reviews:'';
        return response()->json([
            'status' => true,
            'data' => $enrolment,
        ]);
    }


    /**
     * Delete enrolment
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => [
                'required',
                'exists:enrolments,id',
            ],
        ]);

        $user = Auth::user();

        $query = Enrolment::where('id', $request->id);

        if ($user->role === 'vendor') {
            $query->where('vendor_id', $user->id);
        }

        $enrolment = $query->first();

        if (!$enrolment) {
            return response()->json([
                'status' => true,
                'message' => 'Enrolment not found',
            ], 404);
        }


        // Delete files
        if ($enrolment->profile_photo) {
            unlink(public_path($enrolment->profile_photo));
        }

        if ($enrolment->adhar_pdf) {
            unlink(public_path($enrolment->adhar_pdf));

        }

        if ($enrolment->certificate_pdf) {
         unlink(public_path($enrolment->certificate_pdf));

        }


        $enrolment->delete();

        return response()->json([
            'status' => true,
            'message' => 'Enrolment deleted successfully',
        ]);
    }
    /**
     * Bulk Approve
     */
    public function bulkApprove(Request $request)
    {
       $rules = [
            'ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ids.*' => [
                'required',
                'integer',
                'exists:enrolments,id',
            ],
    ];

     $validated = $request->validate($rules);



      $updated = Enrolment::whereIn('id', $validated['ids'])
        ->where('status', 'P')
        ->update([
            'status' => 'A',
            'updated_at' => now(),
        ]);

     return response()->json([
        'status' => true,
        'message' => "{$updated} enrolments record(s) approved successfully.",
        'data' => $updated,
    ]);
}

     /**
     * Bulk Hiring
     */
    public function bulkHiringRequest(Request $request)
    {
        $rules = [
        'ids' => [
            'required',
            'array',
            'min:1',
        ],

        'ids.*' => [
            'required',
            'integer',
            'exists:enrolments,id',
        ],
    ];

    $validated = $request->validate($rules);

    // Hospital ID from JWT authenticated user
    $hospitalId = auth()->id();

    $now = now();

    $applications = collect($validated['ids'])->map(function ($enrolmentId) use ($hospitalId, $now) {
        return [
            'enrolment_id' => $enrolmentId,
            'hospital_id'  => $hospitalId,
            'status'       => 'P',
            'created_at'   => $now,
            'updated_at'   => $now,
        ];
    })->toArray();

    $updated=PlacementApplication::upsert(
        $applications,
        ['enrolment_id', 'hospital_id'],
        ['status', 'updated_at']
    );



    return response()->json([
        'status' => true,
        'message' => count($validated['ids']) . ' enrolment(s) have been submitted and are awaiting admin approval.',
    ]);
 }
     /**
     * Bulk Approve Hiring Request
     */
   public function bulkApproveHiring(Request $request)
    {
        $validated = $request->validate([
            'ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ids.*' => [
                'required',
                'integer',
                'exists:placement_applications,id',
            ],
        ]);

        $ids = $validated['ids'];

        try {

            /*
            * 1. Bulk approve Placement Applications
            */
            $updated = PlacementApplication::whereIn('id', $ids)
                ->update([
                    'status' => 'A',
                    'updated_at' => now(),
                ]);


            /*
            * 2. Get Enrolment IDs belonging to selected applications
            */
            $enrolmentIds = PlacementApplication::whereIn('id', $ids)
                ->whereNotNull('enrolment_id')
                ->pluck('enrolment_id');


            /*
            * 3. Bulk assign hospital_id to Enrolments
            *
            * Each Enrolment gets the hospital_id
            * from its corresponding PlacementApplication.
            */
            Enrolment::whereIn('id', $enrolmentIds)
                ->update([
                    'hospital_id' => PlacementApplication::select('hospital_id')
                        ->whereColumn(
                            'placement_applications.enrolment_id',
                            'enrolments.id'
                        )
                        ->whereIn('placement_applications.id', $ids)
                        ->limit(1),

                    'updated_at' => now(),
                ]);


            return response()->json([
                'status' => true,
                'message' => "{$updated} enrolment(s) have been approved successfully.",
                'data' => $updated,
            ], 200);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve enrolments.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
 /**
     * Toogle Enrolment Status
     */
    public function toogleStatus(Request $request)
    {
        $request->validate([
            'id' => [
                'required',
                'exists:enrolments,id',
            ],
        ]);

        $user = Auth::user();

        $query = Enrolment::where('id', $request->id);

        if ($user->role === 'vendor') {
            $query->where('vendor_id', $user->id);
        }

        $enrolment = $query->first();

        // If status is 'A', change to 'P'. Otherwise, change to 'A'.
        $newStatus = ($enrolment->status === 'A') ? 'P' : 'A';
        $enrolment->update([
            'status' => $newStatus
        ]);

    // Set custom alert messages based on the new status
        $message = ($newStatus === 'A')
            ? 'Enrolment activated successfully.'
            : 'Enrolment paused successfully.';

        return response()->json([
            'status' => true,
            'message' => $message,
        ]);

    }

}
