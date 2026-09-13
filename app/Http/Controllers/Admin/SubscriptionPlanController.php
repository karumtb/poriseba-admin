<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SubscriptionPlanController extends Controller
{
    /**
     * Save / Update Plan
     */
public function save(Request $request)
{
    $rules = [
        'name' => [
            'required',
            'string',
            'max:255',
        ],

        'description' => [
            'nullable',
            'string',
        ],

        'duration' => [
            'required',
            'integer',
            'min:1',
        ],

        'price' => [
            'required',
            'numeric',
            'min:0',
        ],
        'sort_order' => [
            'required',
            'numeric',
            'min:0',
        ],
    ];

    $messages = [
        'name.required' => 'Name is required.',
        'name.string'   => 'Name must be a valid string.',
        'name.max'      => 'Name may not be greater than 255 characters.',

        'description.string' => 'Description must be a valid string.',

        'duration.required' => 'Duration is required.',
        'duration.integer'  => 'Duration must be a valid number.',
        'duration.min'      => 'Duration must be at least 1.',

        'price.required' => 'Price is required.',
        'price.numeric'  => 'Price must be a valid number.',
        'price.min'      => 'Price cannot be negative.',
        'sort_order.required' => 'Sort Order is required.',
        'sort_order.numeric'  => 'Sort Order must be a valid number.',
        'sort_order.min'      => 'Sort Order cannot be negative.',
    ];

    $validated = $request->validate($rules, $messages);

    if ($request->filled('id')) {
        // Edit
        $model = SubscriptionPlan::findOrFail($request->id);

        $model->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Updated successfully.',
            'data' => $model,
        ]);
    }

    // Add
    $model = SubscriptionPlan::create($validated);

    return response()->json([
        'status' => true,
        'message' => 'Created successfully.',
        'data' => $model,
    ]);
}
  /**
     * Get ALL Plans
     */
    public function index()
    {

        try {
            //code...
            // 1. Fetch only root items + eagerly load nested children
            $plans = SubscriptionPlan::orderBy('sort_order', 'asc')

            ->get();


            return response()->json([
            'status' => true,
            'data' => $plans
        ]);

        } catch (\Throwable $th) {
           return response()->json([
            'status' => false,
            'data'=>[],
            'message' => $th->getMessage()
        ]);
        }

    }

     /**
     * Toogle Subscription Plan Status
     */
    public function toogleStatus(Request $request)
    {
        $request->validate([
            'id' => [
                'required',
                'exists:users,id',
            ],
        ]);



        $query = SubscriptionPlan::where('id', $request->id);


        $subscription_plan = $query->first();

        // If status is 'A', change to 'I'. Otherwise, change to 'A'.
        $newStatus = ($subscription_plan->status == 'A') ? 'I' : 'A';
        $subscription_plan->update([
            'status' => $newStatus
        ]);

    // Set custom alert messages based on the new status
        $message = ($newStatus =='A')
            ? 'Subscription Plan activated successfully.'
            : 'Subscription Plan paused successfully.';

        return response()->json([
            'status' => true,
            'message' => $message,
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
                'exists:subscription_plans,id',
            ],
        ]);

        $plan = SubscriptionPlan::where('id',$request->id)->first();
        $plan->delete();

        return response()->json([
            'status' => true,
            'message' => 'Plan deleted successfully',
        ]);
    }

}
