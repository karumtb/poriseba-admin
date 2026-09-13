<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Enrolment;
use App\Models\User;
use App\Models\PlacementApplication;
use App\Models\ViewPlacementApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
     public function index()
     {
        try {
           $user = Auth::user();
        $enrolment_query = Enrolment::query();
        if($user->role=='vendor')
        {
            $enrolment_query->where('vendor_id',$user->id);
        }
        else if($user->role=='hospital')
        {
            $enrolment_query->whereNull('hospital_id');

        }
        $vendor_query = User::query();
        $vendor_query->where('role','vendor');
        $total_enrolment=$enrolment_query->count();
        $total_institute=$vendor_query->count();
        return response()->json([
                'status' => true,
                'data' => ['total_institute'=>$total_institute,'total_enrolment'=>$total_enrolment,
                'subscription_booking'=>0,'total_revenue'=>0],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }



     }
}
