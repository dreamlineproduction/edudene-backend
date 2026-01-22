<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AccountInactiveMailToAllUser;
use App\Mail\SendWarningToAllUser;

use App\Models\School;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $schools = School::with('user:id,full_name,email');

        if (!empty($request->search)) {
            $search = $request->search;

            $schools->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                ->orWhere('school_name', 'like', "%{$search}%")
                ->orWhere('registration_number', 'like', "%{$search}%")
                ->orWhere('year_of_registration', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($user) use ($search) {
                      $user->where('phone_number', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->get('sort_by', 'school_name');
        $sortDirection = $request->get('sort_direction', 'asc');

        if (in_array($sortBy, ['id', 'full_name', 'email', 'timezone', 'login_provider', 'created_at'])) {
            $schools->orderBy($sortBy, $sortDirection);
        } else {
            $schools->orderBy('school_name', 'asc');
        }

        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        $paginated = $schools->paginate($perPage, ['*'], 'page', $page);

        $schools = collect($paginated->items())->map(function ($school) {
            $school->formatted_last_login_datetime = $school->last_login_datetime
                ? formatDisplayDate($school->last_login_datetime, 'd-M-Y H:i:A')
                : null;

            $school->register_datetime = $school->created_at
                ? formatDisplayDate($school->created_at, 'd-M-Y H:i:A')
                : null;


            return $school;
        });

        return jsonResponse(true, 'School fetched successfully', [
            'schools' => $schools,
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $school = School::with('user:id,full_name,email')->find($id);

        if (empty($school)) {
            return jsonResponse(false, 'School not found in our database', null, 404);
        }

        $school->register_datetime = $school->created_at
                ? formatDisplayDate($school->created_at, 'd-M-Y H:i:A')
                : null;

        return jsonResponse(true, 'School fetched successfully', ['school' => $school]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function changeStatus(Request $request, $id){
        $validation = [
            'status' => 'required|in:Active,Inactive,Pending,Decline', 
        ];

        if($request->status === 'Inactive' || $request->status === 'Decline'){
            $validation['description'] = 'required|string';
        }

        $request->validate($validation);

        $school = School::find($id);

        if (empty($school)) {
            return jsonResponse(false, 'School not found in our database', null, 404);
        }

        $school->status = $request->status;
        $school->save();

        // Send mail to user 
        if($request->status === 'Inactive')
        {
            $mailData = [
                'fullName' => $school->user->full_name,
                'schoolName' => $school->school_name,
                'description' => $request->description
            ];
            
            try{
                Mail::to($school->user->email)->send(
                    new AccountInactiveMailToAllUser($mailData)
                );
            } catch (\Exception $e) {
                return jsonResponse(false, 'Something went wrong', [], 500);
            }
           
        }


        return jsonResponse(true, 'Status changed successfully');
    }


    public function sendWarning(Request $request, $id){
        
        $request->validate([
            'description' => 'required|string',          
        ]);

        $school = School::with('user')->find($id);

        if (empty($school)) {
            return jsonResponse(false, 'School not found in our database', null, 404);
        }

        $mailData = [
            'fullName' => $school->user->full_name,
            'description' => $request->description
        ];

        // Send password reset email
        Mail::to($school->user->email)->send(
            new SendWarningToAllUser($mailData)
        );

        return jsonResponse(true, 'Warning sent successfully.');
    }
}
