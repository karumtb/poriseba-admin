<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Save / Update User
     */
    public function save(Request $request)
    {
        $id = $request->input('id');

        $user = $id
            ? User::findOrFail($id)
            : new User();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($id),
            ],

            'phone' => [
                'required',
                'digits:10',
                Rule::unique('users', 'phone')->ignore($id),
            ],
            'role' => [
                'required',
                Rule::in(['hospital', 'vendor']),
            ],
        ];

        // Password required only while creating
        if (!$id) {
            $rules['password'] = [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ];
        } else {
            // Password is optional while editing.
            // But if supplied, it must be strong.
            $rules['password'] = [
                'nullable',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ];
        }

        $validated = $request->validate($rules, [
            'email.unique' => 'This email is already registered.',
            'phone.unique' => 'This phone number is already registered.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain uppercase, lowercase, number and special character.',
            'role.in' => 'The selected role must be either hospital or vendor.',

        ]);

        $user->name =  $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->role =  $validated['role'];

        // Only update password when supplied
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if(empty($id))
        {
                $mail_data=['logo'=>env('PUBLIC_URL').'logo.png','name'=>$user->name,'email'=>$user->email,
                "password"=>$validated['password'],"loginUrl"=>env('APP_URL')];
                Mail::to($user->email)->send(
                    new WelcomeUserMail($mail_data)
                );
        }
        $role=$request->role=='hospital'?'Hiring Organization':'Instititute';
        return response()->json([
            'status' => true,
            'message' => $id
                ? $role.' updated successfully.'
                : $role.' saved successfully.',
            'data' => $user,
        ]);
    }


    /**
     * Get single record
     */
    public function view(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:users,id',
        ]);

        $user = User::findOrFail($request->id);

        return response()->json([
            'status' => true,
            'data' => $user,
        ]);
    }


    /**
     * Paginated listing
     */
    public function listing(Request $request)
    {
        $rules = [

            'role' => [
                'required',
                Rule::in(['hospital', 'vendor']),
            ],
        ];
         $validated = $request->validate($rules, ['role.in' => 'The selected role must be either hospital or vendor.']);
        $perPage = $request->input('per_page', 10);
        $search = trim($request->input('keyword'));

        $query = User::query()
            ->where('role',$request->role)
            ->select([
                'id',
                'name',
                'email',
                'phone',
                'status',
                'created_at',
                'updated_at',
            ]);

        // Search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        $allowedSortColumns = [
            'id',
            'name',
            'email',
            'phone',
            'created_at',
        ];

        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'id';
        }

        $sortOrder = strtolower($sortOrder) === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $users,

        ]);
    }


    /**
     * Delete
     */
    public function delete(Request $request)
    {

         $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:users,id',
        ]);

         if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::findOrFail($request->id);
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'Vendor deleted successfully.',
        ]);
    }

     /**
     * Search Vendor
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required',
        ]);

         if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
        $search=$request->search;
        $vendors = User::query()
        ->where('role', 'vendor')
        ->where('name', 'like', '%' . $search . '%')
        ->select('id', 'name')
        ->get();

        return response()->json([
            'status'=>true,
            'data' => $vendors,
        ]);


    }

    /**
     * Toogle Vendor Status
     */
    public function toogleStatus(Request $request)
    {
        $request->validate([
            'id' => [
                'required',
                'exists:users,id',
            ],
        ]);



        $query = User::where('id', $request->id);


        $user = $query->first();

        // If status is 'A', change to 'I'. Otherwise, change to 'A'.
        $newStatus = ($user->status == 'A') ? 'I' : 'A';
        $user->update([
            'status' => $newStatus
        ]);

    // Set custom alert messages based on the new status
        $message = ($newStatus =='A')
            ? 'Vendor activated successfully.'
            : 'Vendor paused successfully.';

        return response()->json([
            'status' => true,
            'message' => $message,
        ]);




    }
}
