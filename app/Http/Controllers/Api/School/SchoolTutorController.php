<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Mail\School\TutorCreation;
use App\Models\CategoryLevelFour;
use App\Models\School;
use App\Models\SchoolAggrement;
use App\Models\SchoolCategory;
use App\Models\SchoolCategoryLevelFour;
use App\Models\SchoolSubCategory;
use App\Models\SchoolSubSubCategory;
use App\Models\SchoolUser;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SchoolTutorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $loggedInUser = auth('sanctum')->user();

        $users = User::query()
            ->whereIn('id', function ($query) use ($loggedInUser) {
                $query->select('user_id')
                    ->from('school_aggrements')
                    ->where('school_id', $loggedInUser->school->id);
            });

        if ($request->filled('search')) {
            $search = $request->search;

            $users->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('tutor', function ($tutor) use ($search) {
                        $tutor->where('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        $sortBy = $request->get('sort_by', 'full_name');
        $sortDirection = $request->get('sort_direction', 'asc');

        if (in_array($sortBy, ['id', 'full_name', 'email', 'status', 'created_at'])) {
            $users->orderBy($sortBy, $sortDirection);
        } else {
            $users->orderBy('created_at', 'desc');
        }

        $perPage = (int) $request->get('per_page', 10);

        $paginated = $users->with('tutor')->paginate($perPage);

        $users = collect($paginated->items())->map(function ($user) {
            $user->formatted_last_login_datetime = formatDisplayDate($user->last_login_datetime,'j M Y / h:i:s A');

            return $user;
        });

        
        return jsonResponse(true, 'Tutors fetched successfully', [
            'users' => $users,
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

        $request->validate([
            'full_name' => 'required|string|max:255',
            'timezone' => 'required|string|max:150',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|max:50',
            'phone_number' => 'required|string|min:10|max:15|unique:tutors,phone_number',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip' => 'required|string|max:255',
            'ip_agreement' => 'required|string|in:Yes,No',
            'freelancer' => 'required|string|in:Yes,No',
        ]);


        DB::beginTransaction();
        try {

            // Create user
            $user = User::create([
                'role_id' => 4,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => $request->password,
                'status' => $request->status,
                'timezone' => getDefaultTimezone($request->timezone),
            ]);

            // Generate username
            $userName = generateUniqueSlug(
                $request->full_name,
                User::class,
                $user->id,
                'user_name'
            );

            $user->update([
                'user_name' => $userName,
            ]);

            // Tutor details
            Tutor::create([
                'user_id' => $user->id,
                'phone_number' => $request->phone_number,
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'zip' => $request->zip,
                'is_house' => 'Yes'
            ]);

            // School mapping
            $loggedInUser = auth('sanctum')->user()->load('school');

            $schoolAggrement =  SchoolAggrement::create([
                'user_id' => $user->id,
                'school_id' => $loggedInUser->school->id,
                'ip_agreement' => $request->ip_agreement,
                'is_freelancer' => $request->freelancer,
            ]);

            $newPath = 'schools';
            
            // Save 
            if (notEmpty($request->ip_document)) {                            
                $document = finalizeFile($request->ip_document,$newPath);
                $schoolAggrement->update([
                    'agreement_file' => $document['path'],
                    'agreement_file_url' => $document['url']
                ]);

            }

            ///$schoolInfo = School::where('user_id', $school->id)->first();

            // Mail data
            $mailData = [
                'userName' => $userName,
                'fullName' => $request->full_name,
                'email' => $request->email,
                'password' => $request->password, // temporary password
                'schoolName' => $loggedInUser->school->school_name,
                'loginLink' => env('FRONTEND_URL') . '/school/login',
            ];

            Mail::to($request->email)->send(new TutorCreation($mailData));

            DB::commit();

            return jsonResponse(true, 'Tutor account created successfully.', $user, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $loggedInUser = auth('sanctum')->user();

        $user = User::where('id', $loggedInUser->id)
            ->with([
                'tutor',
            ])
            ->first();

        if (!$user || !$user->tutor) {
            return jsonResponse(false, 'Tutor not found in our database', null, 404);
        }

        $data['tutor'] =  array_merge(
            $user->only(['id', 'role_id', 'full_name','email','user_name']),
            $user->tutor->toArray()
        );


        return jsonResponse(true, 'Tutor details', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $loggedInUser = auth('sanctum')->user();
        
        $user = User::where('id', $id)
            ->whereIn('id', function ($query) use ($loggedInUser) {
                $query->select('user_id')
                    ->from('school_aggrements')
                    ->where('school_id', $loggedInUser->id);
            })
            ->with('tutor')
            ->first();
        if (empty($user)) {
            return jsonResponse(false, 'Tutor not found in our database', null, 404);            
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'timezone' => 'required|string|max:150',
            'status' => 'required|in:Active,Inactive',
            'phone_number' => 'required|string|min:10|max:15|unique:tutors,phone_number,' . $user->tutor->id,
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip' => 'required|string|max:255',
        ]);

        $user->update([
            'full_name' => $request->full_name,
            'timezone' => getDefaultTimezone($request->timezone),
            'status' => $request->status,
        ]);

        $user->tutor()->update([
            'phone_number' => $request->phone_number,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'zip' => $request->zip,
        ]);

        return jsonResponse(true, 'Tutor updated successfully.', $user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $loggedInUser = auth('sanctum')->user();
        
        
        $user = User::where('id', $id)
            ->where('role_id', 4)
            ->whereIn('id', function ($query) use ($loggedInUser) {
                $query->select('user_id')
                    ->from('school_aggrements')
                    ->where('school_id', $loggedInUser->id);
            })
            ->first();

        if (empty($user)) {
            return jsonResponse(false, 'Tutor not found in our database', null, 404);
        }
        $user->delete();

        return jsonResponse(true, 'Tutor deleted successfully.');
    }


    public function updateV2(Request $request)
    {
        $loggedInUser = auth('sanctum')->user();

        $user = User::where('id', $loggedInUser->id)
            ->with([
                'tutor',
            ])
            ->first();

        if (!$user || !$user->tutor) {
            return jsonResponse(false, 'Tutor not found in our database', null, 404);
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:users,user_name,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,

            'phone_number' => 'required|string|min:10|max:15|unique:tutors,phone_number,' . $user->tutor->id,
            'about' => 'required|string',
            'what_i_teach' => 'required|string|max:255',
            'education' => 'required|string|max:255',
            'language' => 'required|string|max:255',

            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip' => 'required|string|max:255',

            'highest_qualification' => 'required|string|max:255',
            'university' => 'required|string|max:255',
            'passing_year' => 'required|max:10',

            'facebook_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'x_url' => 'nullable|url',

            //'profile_image' => 'required|integer',
            'timezone' => 'required|string|max:150',
        ]);

        $user->update([
            'full_name' => $request->full_name,
            'timezone' => getDefaultTimezone($request->timezone),
        ]);


        // Save 
        if (notEmpty($request->profile_image)) {

            // Delete Old Profile image from server
            if(!empty($user->tutor->avatar)){
                deleteS3File($user->tutor->avatar);    
            }
            

            $imageArray = finalizeFile($request->profile_image,'schools');
            $user->tutor->update([
                'avatar' => $imageArray['path'],
                'avatar_url' => $imageArray['url']
            ]);
        }

        /** TUTOR UPDATE */
        $user->tutor->update([
            'phone_number' => $request->phone_number,
            'about' => $request->about,
            'what_i_teach' => $request->what_i_teach,
            'education' => $request->education,
            'language' => $request->language,

            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'zip' => $request->zip,

            'highest_qualification' => $request->highest_qualification,
            'university' => $request->university,
            'passing_year' => $request->passing_year,

            'facebook_url' => $request->facebook_url,
            'linkedin_url' => $request->linkedin_url,
            'x_url' => $request->x_url,
        ]);

        
        $user = User::where('id', $user->id)
            ->with([
                'tutor',
            ])
            ->first();

      
        $data['tutor'] =  array_merge(
            $user->only(['id', 'role_id', 'full_name','email','user_name']),
            $user->tutor->toArray()
        );


        return jsonResponse(true, 'Tutor updated successfully.', $data);
    }

	public function saveSchoolSubjects(Request $request)
    {
        try {
            $tutorId = auth('sanctum')->user()->id;

			$schoolInfo = SchoolUser::where('user_id', $tutorId)->first();

			if (!empty($schoolInfo)) {
				$schoolId = $schoolInfo->school_id;
			} else {
				 return jsonResponse(
					false,
					'Error saving tutor subjects',
					['error' => '']
				);
			}

            // Validate request
            $request->validate([
                'subjects' => 'required|array',
                'subjects.*.id' => 'required|integer|exists:category_level_fours,id',
            ]);

            $subjectIds = collect($request->subjects)->pluck('id')->toArray();

            // Delete existing records for this tutor
            SchoolCategory::where('school_id', $tutorId)->delete();
            SchoolSubCategory::where('school_id', $tutorId)->delete();
            SchoolSubSubCategory::where('school_id', $tutorId)->delete();
            SchoolCategoryLevelFour::where('school_id', $tutorId)->delete();

            // Get all unique parent IDs
            $categoryLevelFours = CategoryLevelFour::whereIn('id', $subjectIds)
                ->select('id', 'category_id', 'sub_category_id', 'sub_sub_category_id')
                ->get();

            // Track unique parent IDs to avoid duplicates
            $uniqueCategories = collect();
            $uniqueSubCategories = collect();
            $uniqueSubSubCategories = collect();

            foreach ($categoryLevelFours as $levelFour) {
                // Save to level 4 table
                SchoolCategoryLevelFour::create([
                    'school_id' => $schoolId,
                    'category_id' => $levelFour->id,
                ]);

                // Collect unique parent IDs
                $uniqueCategories->push($levelFour->category_id);
                $uniqueSubCategories->push($levelFour->sub_category_id);
                $uniqueSubSubCategories->push($levelFour->sub_sub_category_id);
            }

            // Save unique parent categories
            foreach ($uniqueCategories->unique() as $categoryId) {
                SchoolCategory::firstOrCreate(
                    [
                        'school_id' => $schoolId,
                        'category_id' => $categoryId,
                    ]
                );
            }

            foreach ($uniqueSubCategories->unique() as $subCategoryId) {
                SchoolSubCategory::firstOrCreate(
                    [
                        'school_id' => $schoolId,
                        'category_id' => $subCategoryId,
                    ]
                );
            }

            foreach ($uniqueSubSubCategories->unique() as $subSubCategoryId) {
                SchoolSubSubCategory::firstOrCreate(
                    [
                        'school_id' => $schoolId,
                        'category_id' => $subSubCategoryId,
                    ]
                );
            }

            return jsonResponse(
                true,
                'School subjects saved successfully',
                [
                    'school_id' => $schoolId,
                    'subjects_count' => count($subjectIds),
                    'saved_subjects' => $subjectIds,
                ]
            );
        } catch (\Exception $e) {
            return jsonResponse(
                false,
                'Error saving tutor subjects',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Fetch tutor's expertise subjects
     */
    public function getTutorSubjects()
    {
        try {
            $tutorId = auth('sanctum')->user()->id;

			$schoolInfo = SchoolUser::where('user_id', $tutorId)->first();

			if (!empty($schoolInfo)) {
				$schoolId = $schoolInfo->school_id;
			} else {
				 return jsonResponse(
					false,
					'Error saving tutor subjects',
					['error' => '']
				);
			}

            // Fetch tutor's subjects with category level four details
            $subjects = SchoolCategoryLevelFour::where('school_id', $schoolId)
                ->with('categoryLevelFour:id,title,category_id,sub_category_id,sub_sub_category_id')
                ->get()
                ->map(function ($tutorSubject) {
                    $levelFour = $tutorSubject->categoryLevelFour;
                    return [
                        'id' => $levelFour->id,
                        'title' => $levelFour->title,
                        'category_id' => $levelFour->category_id,
                        'sub_category_id' => $levelFour->sub_category_id,
                        'sub_sub_category_id' => $levelFour->sub_sub_category_id,
                    ];
                })
                ->values();

            return jsonResponse(
                true,
                'Expertise subjects fetched successfully',
                ['subjects' => $subjects]
            );
        } catch (\Exception $e) {
            return jsonResponse(
                false,
                'Error fetching tutor subjects',
                ['error' => $e->getMessage()]
            );
        }
    }
}
