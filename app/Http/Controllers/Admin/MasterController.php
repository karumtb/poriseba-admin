<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Religion;
use App\Models\Area;
use App\Models\Language;

class MasterController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                'religions' => Religion::select('id', 'name')
                    ->orderBy('name')
                    ->get(),

                'areas' => Area::select('id', 'name')
                    ->orderBy('name')
                    ->get(),

                'languages' => Language::select('id', 'name')
                    ->orderBy('name')
                    ->get(),
            ]
        ]);
    }
}
