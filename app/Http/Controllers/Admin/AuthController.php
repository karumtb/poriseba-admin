<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();



        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password',
            ], 200);
        }

         if($user->status=='I')
        {
             return response()->json([
                'status' => false,
                'message' => 'Your Account has been blocked by Admin.',
            ], 200);
        }
        // Delete old tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }
     /**
     * Update Profile
     */
    public function updateProfile(Request $request)
    {
        try {
          $user = Auth::user();
        /**
         * Validation
         */

        $rules = [
            'profile_photo_file' => [
            'required',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
            ],
        ];

         if ($request->hasFile('profile_photo_file')) {

            // Delete old file if editing
            if ($user->profile_photo && file_exists(public_path($user->profile_photo))) {
                unlink(public_path($user->profile_photo));
            }

            // 1. Ensure target directory exists in public/
            $targetDir = public_path('uploads/profile');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // 2. Upload file
            $file = $request->file('profile_photo_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move($targetDir, $filename);

            $validatedData['profile_image'] = 'uploads/profile/' . $filename;

        }
        else{
         $validatedData['profile_image']=!empty($request->profile_image)?$request->profile_image:null;

        }
         $user->update($validatedData);
            return [
            'status' => true,
            'message' => "Your profile photo has been updated successfully",
            'data' => $user->fresh()
        ];

        } catch (\Throwable $th) {
         // Return the error message, the object, or a custom format
        return [
            'status' => false,
            'message' => $th->getMessage(),
            'code' => $th->getCode()
        ];
        }
    }
    /**
     * Chamge Password
     */
    public function changePassword(Request $request)
    {
        try {
            $user = Auth::user();

            // 1. Password Rule Blueprint (Applied to all three password inputs)
            $passwordRules = [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',       // Must contain at least one uppercase letter
                'regex:/[a-z]/',       // Must contain at least one lowercase letter
                'regex:/[0-9]/',       // Must contain at least one digit
                'regex:/[^A-Za-z0-9]/', // Must contain at least one special character
            ];

            // 2. Validate request parameters
            $validator = Validator::make($request->all(), [
                'old_password'     => $passwordRules,
                'new_password'     => array_merge($passwordRules, ['confirmed']), // Ensures 'new_password_confirmation' matches
                'new_password_confirmation' => ['required', 'string'],
            ], [
                // Custom friendly validation error messages for regex compliance
                'new_password.regex' => 'The password must contain uppercase, lowercase, numbers, and special characters.',
            ]);

            if ($validator->fails()) {
                return [
                    'status'  => false,
                    'message' => 'Validation error occur',
                    'errors'  => $validator->errors()->all(),
                    'code'    => 422
                ];
            }

            // 3. Verify that the user provided their correct current password
            if (!Hash::check($request->old_password, $user->password)) {
                return [
                    'status'  => false,
                    'message' => 'The old password you entered is incorrect.',
                    'code'    => 400
                ];
            }

            // 4. Update the user database instance with the newly hashed password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return [
                'status'  => true,
                'message' => "Your password has been changed successfully",
                'data'    => $user->fresh()
            ];

        } catch (\Throwable $th) {
            // Return fallback block handler matching your architectural setup
            return [
                'status'  => false,
                'message' => $th->getMessage(),
                'code'    => $th->getCode() ?: 500
            ];
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout successful',
        ], 200);
    }
  }
