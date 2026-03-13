<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use Illuminate\Http\Request;

class TutorProfileController extends Controller
{
    public function show()
	{
		
		$loggedInUser = auth('sanctum')->user();

        $teacher = $loggedInUser
            ->tutor()
            ->first();

        if (!$teacher) {
            return jsonResponse(false, 'Teacher not found in our database.', null, 404);
        }

        $data['tutor'] =  array_merge(
            $loggedInUser->only(['id', 'role_id', 'full_name','email','user_name']),
            $teacher->toArray()
        );


        return jsonResponse(true, 'Teacher details', $data);
	}


	public function update(Request $request, string $id)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return jsonResponse(false, 'Unauthorized', null, 401);
        }

        $teacher = $user->tutor()->first();

        $request->validate([
		    'user_name' => 'required|string|max:100',
		    'full_name' => 'required|string|max:200',
		    'email' => 'required|email|max:200',

		    'about' => 'required|string',

		    'address_line_1' => 'required|string|max:200',
		    'address_line_2' => 'nullable|string|max:200',

		    'phone_number' => 'required|string|max:15',

		    'city' => 'required|string|max:200',
		    'state' => 'required|string|max:200',
		    'country' => 'required|string|max:200',
		    'zip' => 'required|string|max:20',

		    'passing_year' => 'nullable|integer|digits:4',

		    'highest_qualification' => 'nullable|string|max:200',
		    'university' => 'nullable|string|max:200',

		    'year_of_experience' => 'nullable|integer|min:0|max:60',

		    // Social links
		    'facebook_url' => 'nullable|url',
		    'linkedin_url' => 'nullable|url',
		    'instagram_url' => 'nullable|url',
		    'youtube_url' => 'nullable|url',
		    'x_url' => 'nullable|url',

		    // Image
		    'avatar' => 'nullable|integer',
		    'profileImage' => 'nullable|integer'
		]);

        
        $user->update([
            'full_name' => $request->full_name,
            //'email' => $request->email,
        ]);

        // $schoolSlug = generateUniqueSlug(
        //     $request->school_name,
        //     School::class,
        //     $user->id,
        //     'school_slug',
        //     '-'
        // );

        $teacherData = $request->only([
		    'about',
		    'address_line_1',
		    'address_line_2',
		    'phone_number',
		    'city',
		    'state',
		    'country',
		    'zip',
		    'passing_year',
		    'year_of_registration',
		    'highest_qualification',
		    'university',
		    'facebook_url',
		    'linkedin_url',
		    'instagram_url',
		    'youtube_url',
		    'x_url',
		]);

        $teacherData['user_id'] = $user->id;

        /** Handle logo upload */
        if ($request->filled('avatar')) {

            if ($teacher && $teacher->avatar) {
                deleteS3File($teacher->avatar);
            }

            $document = finalizeFile($request->avatar, 'tutors');

            //dd($document);

            $teacherData['avatar'] = $document['path'];
            $teacherData['avatar_url'] = $document['url'];
        }

        /** Create or update school */
        $teacher = Tutor::updateOrCreate(
            ['user_id' => $user->id],
            $teacherData
        );

        /** Clean response */
        $data['tutor'] = array_merge(
            $user->only(['id', 'role_id', 'full_name', 'email', 'user_name']),
            $teacher->toArray()
        );

        return jsonResponse(true, 'Profile has been updated successfully', $data);
    }
}
