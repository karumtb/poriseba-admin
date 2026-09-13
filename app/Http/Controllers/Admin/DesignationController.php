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
            $designations=Designation::get();
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
}
