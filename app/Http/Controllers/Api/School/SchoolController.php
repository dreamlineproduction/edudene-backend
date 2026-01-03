<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Course;
use App\Models\School;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schools  = School::where('status', 'Active')
            ->with('user:id,full_name,email')
            ->withCount('tutors')
            ->withCount('courses')
            ->withCount('classes')
            ->get();

        $schools = $schools->map(function ($school) {
            $school->short_description = shortDescription($school->about_us, 100);
            return $school;
        });


        $data['schools'] = $schools;
        return jsonResponse(true, 'Schools', $data);
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
        $loggedInUser = auth('sanctum')->user();

        $school = $loggedInUser
            ->school()
            ->with('user:id,full_name,email')
            ->first();

        if (!$school) {
            return jsonResponse(false, 'School not found in our database.', null, 404);
        }

        return jsonResponse(true, 'School details', [
            'school' => $school
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $loggedInUser = auth('sanctum')->user();

        $request->validate([
            'school_name' => 'required|string|max:200',
            'about_us' => 'required|string|max:200',
            'address_line_1' => 'required|string|max:200',
            'phone_number' => 'required|string|max:15',            
            'city' => 'required|string|max:200',
            'country' => 'required|string|max:200',
            'state' => 'required|string|max:200',
            'zip' => 'required|string|max:200',
            'year_of_registration' => 'required|digits:4|integer',                        
            'stripe_email' => 'required|email'                        
        ]);


        $user = User::where('id', $loggedInUser->id)->first();
        if(empty($user)){
            return jsonResponse(false, 'User not found.',404);
        }

        $schoolSlug = generateUniqueSlug($request->school_name, 'App\Models\School', $user->id, 'school_slug','-');

        // Update other profile information
        $request->merge([
            'school_slug' => $schoolSlug
        ]);


        $newPath = $user->id;
        // Save 
        if (notEmpty($request->logo)) {                            
            $document = finalizeFile($request->logo,$newPath);
            $request->merge([
                'logo' => $document['path'],
                'logo_url' => $document['url']
            ]);
        }

        School::updateOrCreate(['user_id' => $user->id], $request->toArray());

        return jsonResponse(true, 'School profile updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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


    public function showFront(string $id)
    {
        $q  = School::query();

        if(is_numeric($id)){
            $q->where('id', $id);
        } else {
            $q->where('school_slug', $id);
        }

        $school = $q->where('status', 'Active')               
            ->with([
                'user:id,full_name,email',
                'tutors',
                'courses',
                'classes.class_type:id,title',
                'classes.category:id,title',
                'classes.sub_category:id,title',
                'classes.sub_sub_category:id,title',
                'classes.category_level_four:id,title',
                'classes.tutor:id,full_name,email'
            ])
            ->withCount('tutors')
            ->withCount('courses')               
            ->withCount('classes')
            ->first();

        if (!$school) {
            return jsonResponse(false, 'School not found in our database.', null, 404);
        }



        $school->classes->map(function ($class) {
            $class->formatted_start_date = formatDisplayDate($class->start_date,'d-M-Y');
            $class->formatted_end_date = formatDisplayDate($class->end_date,'d-M-Y');

            $class->tutor->about  = null;
            $class->tutor->avatar  = null;
            $tutorInfo = Tutor::select('about','avatar')->where('id', $class->tutor_id)->first();
            if(!empty($tutorInfo) && !empty($tutorInfo->about)){
                $class->tutor->about = $tutorInfo->about;
            }
            if(!empty($tutorInfo) && !empty($tutorInfo->avatar)){
                $class->tutor->avatar = $tutorInfo->avatar;
            }
           
               


            $duration = calculateDuration($class->start_date,$class->end_date);
            if (!$duration) {
                $class->formatted_duration = null;
            }

            $parts = [];
            if ($duration['years'] > 0) {
                $parts[] = $duration['years'] . ' ' . ($duration['years'] > 1 ? 'Years' : 'Year');
            }

            if ($duration['months'] > 0) {
                $parts[] = $duration['months'] . ' ' . ($duration['months'] > 1 ? 'Months' : 'Month');
            }

            if ($duration['total_days'] > 0) {
                $parts[] = $duration['total_days'] . ' ' . ($duration['days'] > 1 ? 'Days' : 'Day');
            }

            $class->formatted_duration = implode(', ', $parts);
            return $class;
        });
       
        $school->tutors->map(function ($tutor) {
            $tutor->total_reviews = 980;
            $tutor->avg_rating = 4.8;
            $tutor->hourly_rate = 80;

            $tutor->total_courses = Course::where('user_id', $tutor->id)->count();
            $tutor->total_classes = Classes::where('tutor_id', $tutor->id)->count();
            return $tutor;
        });

        $school->short_description = shortDescription($school->about_us, 100);
        $school->profile_created = formatDisplayDate($school->created_at, 'Y');

        $school->total_reviews = 659;
        $school->avg_rating = 4.9;

        

        $data['schools'] = $school;
        return jsonResponse(true, 'Schools', $data);
    }
}
