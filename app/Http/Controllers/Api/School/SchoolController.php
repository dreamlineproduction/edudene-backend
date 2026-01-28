<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\ClassSessions;
use App\Models\Course;
use App\Models\School;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SchoolController extends Controller
{
    // /**
    //  * Display a listing of the resource.
    //  */
    // public function index()
    // {
    //     $schools  = School::where('status', 'Active')
    //         ->with('user:id,full_name,email')
    //         ->withCount('tutors')
    //         ->withCount('courses')
    //         ->withCount('classes')
    //         ->get();

    //     $schools = $schools->map(function ($school) {
    //         $school->short_description = shortDescription($school->about_us, 100);
    //         return $school;
    //     });


    //     $data['schools'] = $schools;
    //     return jsonResponse(true, 'Schools', $data);
    // }

   

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $loggedInUser = auth('sanctum')->user();

        $school = $loggedInUser
            ->school()
            ->first();

        if (!$school) {
            return jsonResponse(false, 'School not found in our database.', null, 404);
        }

        $data['school'] =  array_merge(
            $loggedInUser->only(['id', 'role_id', 'full_name','email','user_name']),
            $school->toArray()
        );


        return jsonResponse(true, 'School details', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return jsonResponse(false, 'Unauthorized', null, 401);
        }

        $school = $user->school()->first();

        $request->validate([
            'school_name' => 'required|string|max:200',
            'about_us' => 'required|string',
            'address_line_1' => 'required|string|max:200',
            'address_line_2' => 'nullable|string|max:200',
            'phone_number' => 'required|string|max:15',
            'city' => 'required|string|max:200',
            'country' => 'required|string|max:200',
            'state' => 'required|string|max:200',
            'zip' => 'required|string|max:200',
            'year_of_registration' => 'required|digits:4',
            'stripe_email' => 'required|email',

            // optional socials
            'website' => 'nullable|url',
            'facebook' => 'nullable|url',
            'linkedin' => 'nullable|url',
            'instagram' => 'nullable|url',
            'youtube' => 'nullable|url',
            'x' => 'nullable|url',
            'vimeo' => 'nullable|url',
            'pinterest' => 'nullable|url',
            'github' => 'nullable|url',
        ]);

        $user->update([
            'full_name' => $request->full_name,
            'email' => $request->email,
        ]);

        $schoolSlug = generateUniqueSlug(
            $request->school_name,
            School::class,
            $user->id,
            'school_slug',
            '-'
        );

        $schoolData = $request->only([
            'school_name',
            'about_us',
            'address_line_1',
            'address_line_2',
            'phone_number',
            'city',
            'state',
            'country',
            'zip',
            'website',
            'year_of_registration',
            'stripe_email',
            'facebook',
            'linkedin',
            'instagram',
            'youtube',
            'x',
            'vimeo',
            'pinterest',
            'github',
        ]);

        $schoolData['school_slug'] = $schoolSlug;
        $schoolData['user_id'] = $user->id;

        /** Handle logo upload */
        if ($request->filled('logo')) {

            if ($school && $school->logo) {
                deleteS3File($school->logo);
            }

            $document = finalizeFile($request->logo, 'schools');

            $schoolData['logo'] = $document['path'];
            $schoolData['logo_url'] = $document['url'];
        }

        /** Create or update school */
        $school = School::updateOrCreate(
            ['user_id' => $user->id],
            $schoolData
        );

        /** Clean response */
        $data['school'] = array_merge(
            $user->only(['id', 'role_id', 'full_name', 'email', 'user_name']),
            $school->toArray()
        );

        return jsonResponse(true, 'School profile updated successfully', $data);
    }


    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required',
        ]);
        
        $user = auth('sanctum')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return jsonResponse(false, "We couldn't verify your current password. If you forgot it, use “Forgot password”", null,   );
        }

        if($request->current_password === $request->new_password){
            return jsonResponse(false, "The new password cannot be the same as the current password.", null, 400);
        }
        

        // Update new password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
        
        return jsonResponse(true, 'Password changed successfully.', null, 200);          
    }     

}
