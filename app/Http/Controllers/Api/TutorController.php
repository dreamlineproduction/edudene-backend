<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use DateTime;
use App\Models\User;
use App\Models\Course;
use App\Models\Tutor;
use App\Models\Classes;
use App\Models\CategoryLevelFour;
use App\Models\OneOnOneClassSlot;
use App\Models\TutorCategory;
use App\Models\TutorSubCategory;
use App\Models\TutorSubSubCategory;
use App\Models\TutorCategoryLevelFour;
use Illuminate\Http\Request;

class TutorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $page = (int) $request->get('page', 1);

        $categoryIds            = $request->category_id ? explode(',', $request->category_id) : [];
        $subCategoryIds         = $request->sub_category_id ? explode(',', $request->sub_category_id) : [];
        $subSubCategoryIds      = $request->sub_sub_category_id ? explode(',', $request->sub_sub_category_id) : [];
        $levelFourCategoryIds   = $request->sub_sub_sub_category_id ? explode(',', $request->sub_sub_sub_category_id) : [];
        $search                 = trim($request->search);


        $query = User::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                foreach (explode(' ', $search) as $word) {
                    $q->where('full_name', 'LIKE', "%{$word}%");
                }
            });
        }

        $query->whereIn('role_id',[2,4])
            ->with('tutor:id,user_id,avatar,avatar_url')
            ->withCount(['course'])
            ->latest();

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $tutors = collect($paginated->items())->map(function ($teacher) {
            $teacher->formatted_created_at = $teacher->created_at ? formatDisplayDate($teacher->created_at, 'd-M-Y H:i:A'): null;
            
            $teacher->total_reviews = 980;
            $teacher->avg_rating = 4.8;
            $teacher->hourly_rate = rand(50,250);

            $teacher->total_courses = Course::where('user_id', $teacher->id)->count();
            $teacher->total_classes = Classes::where('tutor_id', $teacher->id)->count();

            return $teacher;
        });

        return jsonResponse(true, 'User fetched successfully', [
            'tutors' => $tutors,
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }


    /**
     * Display a listing of the resource.
     */
    public function popularTeacher(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);


        $query = User::query();
        $query->whereIn('role_id',[2,4])
            ->with('tutor:id,user_id,avatar,avatar_url')
            ->withCount(['course'])
            ->orderBy('full_name','asc');

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $tutors = collect($paginated->items())->map(function ($teacher) {
            $teacher->formatted_created_at = $teacher->created_at ? formatDisplayDate($teacher->created_at, 'd-M-Y H:i:A'): null;
            
            $teacher->total_reviews = 980;
            $teacher->avg_rating = 4.8;
            $teacher->hourly_rate = rand(50,250);

            $teacher->total_courses = Course::where('user_id', $teacher->id)->count();
            $teacher->total_classes = Classes::where('tutor_id', $teacher->id)->count();

            return $teacher;
        });

        return jsonResponse(true, 'User fetched successfully', [
            'tutors' => $tutors,
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }


    public function show(Request $request, $id)
    {
        $user = User::where('role_id', 2)->where('status', 'Active');
        if(is_numeric($id)){
            $user->where('id', $id);
        } else {
            $user->where('user_name', $id);
        }

        $user = $user->first(); 

        if (!$user) {
            return jsonResponse(false, 'Tutor not found in our database.', null, 404);
        }

        

        $teacher  = Tutor::where('user_id', $user->id);
        
        $teacher = $teacher->with([
            'user:id,full_name,email',
        ])
        ->with('school')
        ->withCount([
            'courses as courses_count' => function ($query) {
                $query->where('status', 'Active');
            }
        ])          
        ->withCount([
            'classes as classes_count' => function ($query) {
                $query->where('status', 'Approved');
            }
        ])
        ->first();

        if (!$teacher) {
            return jsonResponse(false, 'Tutor not found in our database.', null, 404);
        }

        $teacher->short_description = shortDescription($teacher->about_us, 100);
        $teacher->profile_created = formatDisplayDate($teacher->created_at, 'Y');

        $teacher->total_reviews = 80;
        $teacher->avg_rating = 4.9;

        

        $data['teacher'] = $teacher;
        return jsonResponse(true, 'Fetch teacher successfully', $data);
    }


    public function classes(Request $request, $id)
    {
        $today = (new \DateTime())->format('Y-m-d');

        $user = User::where('role_id', 2)->where('status', 'Active');
        if(is_numeric($id)){
            $user->where('id', $id);
        } else {
            $user->where('user_name', $id);
        }

        $user = $user->first(); 

        if (!$user) {
            return jsonResponse(false, 'Tutor not found in our database.', null, 404);
        }


        $query = Classes::query();

        $query->with([
            'class_type:id,title,status',
            'category:id,title',
            'sub_category:id,title',
            'sub_sub_category:id,title',
            'category_level_four:id,title',
            'tutor:id,full_name,email',   
            'school:id,user_id,school_name,school_slug',
            'school.user:id,full_name,avatar',
        ]);

        $query->where(['tutor_id'=>$user->id,'status' => 'Approved']);
        //$query->whereDate('end_date', '>=', $today);

        $perPage = $request->get('per_page', 10);
        $classes = $query->paginate($perPage);

        $classes = collect($classes->items())->map(function ($class) {
            $class->formatted_start_date = formatDisplayDate($class->start_date,'d/m/Y');
            $class->formatted_end_date = formatDisplayDate($class->end_date,'d/m/Y');

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

        $data['classes'] = $classes;
        return jsonResponse(true, 'Tutor Classes list', $data);
    }

    public function course(Request $request, $id)
    {
        $user = User::where('role_id', 2)->where('status', 'Active');
        if(is_numeric($id)){
            $user->where('id', $id);
        } else {
            $user->where('user_name', $id);
        }

        $user = $user->first(); 

        if (!$user) {
            return jsonResponse(false, 'Tutor not found in our database.', null, 404);
        }


        $query = Course::query();
        $query->with([
            'user:id,full_name',
            'school:id,school_name',           
            'courseAsset',
            'reviews'
        ])
        ->where(['user_id'=>$user->id,'status' => 'Active'])
        ->withAvg('reviews', 'rating')
        ->withCount('reviews');

        $perPage = $request->get('per_page', 10);
        $courses = $query->paginate($perPage);

        $courses = collect($courses->items())->map(function ($course) {
            $course->avg_rating = 4.5;
            $course->review_count = rand(1,5);
            $course->enrollment_count = rand(100,500);

            
            return $course;
        });

        $data['courses'] = $courses;
        return jsonResponse(true, 'Course list', $data);
    }


    public function getMonthWiseSlots($id)
    {
        $user = User::where('role_id', 2)->where('status', 'Active');
        if(is_numeric($id)){
            $user->where('id', $id);
        } else {
            $user->where('user_name', $id);
        }

        $user = $user->first();

        if (!$user) {
            return jsonResponse(false, 'Tutor not found in our database.', null, 404);
        }

        $currentYear  = (int) date('Y');
        $currentMonth = (int) date('m');

        $startDate = new DateTime("$currentYear-$currentMonth-01");
        $endDate   = new DateTime("$currentYear-12-31");

        $slots = OneOnOneClassSlot::where('tutor_id', $user->id)
            ->where('is_active', 1)
            ->whereBetween('class_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ])
            ->selectRaw('YEAR(class_date) as year, MONTH(class_date) as month, COUNT(*) as total_slots')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Normalize months till December
        $months = [];
        $cursor = clone $startDate;

        while ($cursor <= $endDate) {
            $key = $cursor->format('Y-n');

            $months[$key] = [
                'year'  => (int) $cursor->format('Y'),
                'month' => (int) $cursor->format('n'),
                'total_slots' => 0
            ];

            $cursor->modify('+1 month');
        }

        foreach ($slots as $slot) {
            $key = $slot->year . '-' . $slot->month;
            if (isset($months[$key])) {
                $months[$key]['total_slots'] = (int) $slot->total_slots;
            }
        }

       
        return jsonResponse(true, 'Count Months list', [
            'months' => array_values($months)
        ]);
    }


    public function getOneOnOneCalendar(Request $request, $id) 
    {
        $user = User::where('role_id', 2)->where('status', 'Active');
        if(is_numeric($id)){
            $user->where('id', $id);
        } else {
            $user->where('user_name', $id);
        }

        $user = $user->first();

        if (!$user) {
            return jsonResponse(false, 'Tutor not found in our database.', null, 404);
        }
        

        $month = (int) ($request->get('month') ?? date('m'));
        $year  = (int) ($request->get('year') ?? date('Y'));

        $startOfMonth = new DateTime("$year-$month-01 00:00:00");

        // End of month
        $endOfMonth = clone $startOfMonth;
        $endOfMonth->modify('last day of this month')->setTime(23, 59, 59);

        $slots = OneOnOneClassSlot::where('tutor_id', $user->id)
            ->where('is_active', 1)
            ->whereBetween('class_date', [
                $startOfMonth->format('Y-m-d'),
                $endOfMonth->format('Y-m-d')
            ])
            ->select('class_date')
            ->groupBy('class_date')
            ->get();

        $availableDates = $slots->pluck('class_date')
        ->map(fn ($d) => (string) $d)
        ->values();



        // $oneOnOneClasses = OneOnOneClassSlot::where('tutor_id', $user->id)->get(); 

        return jsonResponse(true, 'OneOnOneClass list', [
            'month' => $month,
            'year' => $year,
            'available_dates' => $availableDates
        ]);
    }

    public function oneOnOneSlot(Request $request, $id,$classDate = null) 
    {
        $user = User::where('role_id', 2)->where('status', 'Active');
        if(is_numeric($id)){
            $user->where('id', $id);
        } else {
            $user->where('user_name', $id);
        }

        $user = $user->first();

        if (!$user) {
            return jsonResponse(false, 'Tutor not found in our database.', null, 404);
        }

        $categories = [];

        $categories = TutorCategoryLevelFour::select('id','category_id')->where('tutor_id',$user->id)->with([
            'categoryLevelFour:id,title,slug'
        ])->get();

        $slots = OneOnOneClassSlot::where('tutor_id', $user->id)
            ->where('is_active', 1)
            ->where('class_date', '=',$classDate)
            ->get()
            ->map(function ($slot) use ($categories) {
                $startTime = new DateTime($slot->start_time);
                $endTime = new DateTime($slot->end_time);

                $slot->start_time_formatted = $startTime->format('h:i A');
                $slot->end_time_formatted = $endTime->format('h:i A');

                $interval = $startTime->diff($endTime);

                $slot->total_hours = ($interval->days * 24) + $interval->h + ($interval->i / 60);


                $slot->categories = $categories;
                return $slot;

            });

        if($slots->isNotEmpty())    
        {
            return jsonResponse(true, 'fetching slots', [
                'slots'=>$slots
            ]);
        }

        return jsonResponse(true, 'fetching slots',[]);
    }



    /**
     * Save tutor's selected subjects/categories
     * Payload: { subjects: [{id: 4}, {id: 2}, {id: 5}] }
     * These IDs are CategoryLevelFour (last level) IDs
     * Parent categories are saved automatically
     */
    public function saveTutorSubjects(Request $request)
    {
        try {
            $tutorId = auth('sanctum')->user()->id;

            // Validate request
            $request->validate([
                'subjects' => 'required|array',
                'subjects.*.id' => 'required|integer|exists:category_level_fours,id',
            ]);

            $subjectIds = collect($request->subjects)->pluck('id')->toArray();

            // Delete existing records for this tutor
            TutorCategory::where('tutor_id', $tutorId)->delete();
            TutorSubCategory::where('tutor_id', $tutorId)->delete();
            TutorSubSubCategory::where('tutor_id', $tutorId)->delete();
            TutorCategoryLevelFour::where('tutor_id', $tutorId)->delete();

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
                TutorCategoryLevelFour::create([
                    'tutor_id' => $tutorId,
                    'category_id' => $levelFour->id,
                ]);

                // Collect unique parent IDs
                $uniqueCategories->push($levelFour->category_id);
                $uniqueSubCategories->push($levelFour->sub_category_id);
                $uniqueSubSubCategories->push($levelFour->sub_sub_category_id);
            }

            // Save unique parent categories
            foreach ($uniqueCategories->unique() as $categoryId) {
                TutorCategory::firstOrCreate(
                    [
                        'tutor_id' => $tutorId,
                        'category_id' => $categoryId,
                    ]
                );
            }

            foreach ($uniqueSubCategories->unique() as $subCategoryId) {
                TutorSubCategory::firstOrCreate(
                    [
                        'tutor_id' => $tutorId,
                        'category_id' => $subCategoryId,
                    ]
                );
            }

            foreach ($uniqueSubSubCategories->unique() as $subSubCategoryId) {
                TutorSubSubCategory::firstOrCreate(
                    [
                        'tutor_id' => $tutorId,
                        'category_id' => $subSubCategoryId,
                    ]
                );
            }

            return jsonResponse(
                true,
                'Tutor subjects saved successfully',
                [
                    'tutor_id' => $tutorId,
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

            // Fetch tutor's subjects with category level four details
            $subjects = TutorCategoryLevelFour::where('tutor_id', $tutorId)
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
