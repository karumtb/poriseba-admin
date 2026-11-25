<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Designation;

class DesignationController extends Controller
{
    /**
     *  listing
     */
    public function listing(Request $request)
    {
        try {
            $designations = Designation::get();
            return response()->json([
                'status' => true,
                'data' => $designations,

            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(), // Extracts the exact error message text
            ]);
        }
    }
    public function save(Request $request)
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],


        ];

        $messages = [
            'name.required' => 'Name is required.',
            'name.string'   => 'Name must be a valid string.',
            'name.max'      => 'Name may not be greater than 255 characters.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($request->filled('id')) {
            // Edit
            $model = Designation::findOrFail($request->id);

            $model->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Updated successfully.',
                'data' => $model,
            ]);
        }

        // Add
        $model = Designation::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Created successfully.',
            'data' => $model,
        ]);
    }
}
