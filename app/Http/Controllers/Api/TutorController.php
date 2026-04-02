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
        $search = trim($request->search);
        $sortBy = trim($request->sort_by);

        $categoryIds            = $request->category_id ? explode(',', $request->category_id) : [];
        $subCategoryIds         = $request->sub_category_id ? explode(',', $request->sub_category_id) : [];
        $subSubCategoryIds      = $request->sub_sub_category_id ? explode(',', $request->sub_sub_category_id) : [];
        $levelFourCategoryIds   = $request->sub_sub_sub_category_id ? explode(',', $request->sub_sub_sub_category_id) : [];

        $countryName = $request->country;
        $stateName   = $request->state;

        $startPrice = $request->start_price;
        $endPrice   = $request->end_price;

        $hasFilter = !empty($categoryIds) || !empty($subCategoryIds) || !empty($subSubCategoryIds) || !empty($levelFourCategoryIds);

        $query = User::query()
            ->select('users.*',
                'tutors.id',
                'tutors.user_id',
                'tutors.avatar',
                'tutors.avatar_url',
                'tutors.country',
                'tutors.state',
                'tutors.one_to_one_hourly_rate',
                'tutors.what_i_teach'
            )
            ->leftJoin('tutors', 'users.id', '=', 'tutors.user_id')

            // Category joins
            ->leftJoin('tutor_category', 'tutors.user_id', '=', 'tutor_category.tutor_id')
            ->leftJoin('tutor_sub_category', 'tutors.user_id', '=', 'tutor_sub_category.tutor_id')
            ->leftJoin('tutor_sub_sub_category', 'tutors.user_id', '=', 'tutor_sub_sub_category.tutor_id')
            ->leftJoin('tutor_category_level_four', 'tutors.user_id', '=', 'tutor_category_level_four.tutor_id')

            ->whereIn('users.role_id', [2, 4])
            ->whereNotNull('tutors.id');

        // Search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                foreach (explode(' ', $search) as $word) {
                    $q->where('users.full_name', 'LIKE', "%{$word}%");
                }
            });
        }

        //Country & State filter
        if (!empty($countryName)) {
            $query->where('tutors.country', $countryName);
        }

        if (!empty($stateName)) {
            $query->where('tutors.state', $stateName);
        }

        if (!empty($startPrice)) {
            $query->where('tutors.one_to_one_hourly_rate', '>=', $startPrice);
        }

        if (!empty($endPrice)) {
            $query->where('tutors.one_to_one_hourly_rate', '<=', $endPrice);
        }

        //Category filters
        if ($hasFilter) {
            $query->where(function ($q) use (
                $categoryIds,
                $subCategoryIds,
                $subSubCategoryIds,
                $levelFourCategoryIds
            ) {

                if (!empty($levelFourCategoryIds)) {
                    $q->whereIn('tutor_category_level_four.category_id', $levelFourCategoryIds);
                } elseif (!empty($subSubCategoryIds)) {
                    $q->whereIn('tutor_sub_sub_category.category_id', $subSubCategoryIds);
                } elseif (!empty($subCategoryIds)) {
                    $q->whereIn('tutor_sub_category.category_id', $subCategoryIds);
                } elseif (!empty($categoryIds)) {
                    $q->whereIn('tutor_category.category_id', $categoryIds);
                }
            });
        }

        // Course Count 
        $query->addSelect([
            'course_count' => Course::selectRaw('COUNT(*)')
                ->where('status','Active')
                ->whereColumn('courses.user_id', 'users.id')
        ]);

        // Course Count 
        $query->addSelect([
            'class_count' => Classes::selectRaw('COUNT(*)')
                ->where('status','Active')
                ->whereColumn('classes.tutor_id', 'users.id')
        ]);

        // Sorting
        if ($sortBy === 'name_desc') {
            $query->orderBy('users.full_name', 'DESC');
        } elseif ($sortBy === 'name_asc') {
            $query->orderBy('users.full_name', 'ASC');
        } else {
            $query->orderBy('users.created_at', 'DESC');
        }

        // Duplicate fix
        $query->distinct('users.id');

        // Pagination
        $tutors = $query->paginate($perPage);

        // Transform data
        $tutors->getCollection()->transform(function ($teacher) {
            $teacher->formatted_created_at = $teacher->created_at 
                ? formatDisplayDate($teacher->created_at, 'd-M-Y H:i:A') 
                : null;
            return $teacher;
        });

        return jsonResponse(true, 'Tutor fetched successfully', [
            'tutors' => $tutors->items(),
            'total' => $tutors->total(),
            'current_page' => $tutors->currentPage(),
            'per_page' => $tutors->perPage(),
            'last_page' => $tutors->lastPage(),
        ]);
    }


    /**
     * Display a listing of the resource.
     */
    public function popularTeacher(Request $request)
    {
        $perPage        = (int) $request->get('per_page', 50);
        $page           = (int) $request->get('page', 1);

        $categoryIds    = $request->category_id ? explode(',', $request->category_id) : [];
        $subCategoryIds = $request->sub_category_id ? explode(',', $request->sub_category_id) : [];
        $hasFilter = !empty($categoryIds) || !empty($subCategoryIds);

        $query = User::query()
            ->select('users.*',
                'tutors.id',
                'tutors.user_id',
                'tutors.avatar',
                'tutors.avatar_url',
                'tutors.country',
                'tutors.state',
                'tutors.one_to_one_hourly_rate',
                'tutors.what_i_teach'
            )
            ->whereIn('role_id', [2, 4])
            ->withCount(['course'])
            ->leftJoin('tutors', 'users.id', '=', 'tutors.user_id')

            // Category joins
            ->leftJoin('tutor_category', 'tutors.user_id', '=', 'tutor_category.tutor_id')
            ->leftJoin('tutor_sub_category', 'tutors.user_id', '=', 'tutor_sub_category.tutor_id');
           
        //Category filters
        if ($hasFilter) {
            $query->where(function ($q) use (
                $categoryIds,
                $subCategoryIds,
            ) {
                if (!empty($subCategoryIds)) {
                    $q->whereIn('tutor_sub_category.category_id', $subCategoryIds);
                } else if (!empty($categoryIds)) {
                    $q->whereIn('tutor_category.category_id', $categoryIds);
                }
            });
        }
        
        // Course Count (optimized)
        $query->addSelect([
            'course_count' => Course::selectRaw('COUNT(*)')
                ->where('status','Active')
                ->whereColumn('courses.user_id', 'users.id')
        ]);

        // Duplicate fix
        $query->distinct('users.id');

        // Pagination
        $tutors = $query->paginate($perPage);

        // Transform data
        $tutors->getCollection()->transform(function ($teacher) {
            $teacher->formatted_created_at = $teacher->created_at 
                ? formatDisplayDate($teacher->created_at, 'd-M-Y H:i:A') 
                : null;

            // optimize if needed later
            $teacher->total_classes = Classes::where('tutor_id', $teacher->id)->count();

            return $teacher;
        });


        return jsonResponse(true, 'User fetched successfully', [
            'tutors' => $tutors->items(),
            'total' => $tutors->total(),
            'current_page' => $tutors->currentPage(),
            'per_page' => $tutors->perPage(),
            'last_page' => $tutors->lastPage(),
        ]);
    }


    public function show(Request $request, $id)
    {
        $user = User::whereIn('role_id', [2, 4])->where('status', 'Active');

        if (is_numeric($id)) {
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
            'user:id,full_name,email,user_name',
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
        $teacher->profile_created = formatDisplayDate($teacher->created_at, 'd M Y');

        $teacher->total_reviews = 0;
        $teacher->avg_rating = 0;

        $data['teacher'] = $teacher;
        return jsonResponse(true, 'Fetch teacher successfully', $data);
    }


    public function classes(Request $request, $id)
    {        
        $perPage = (int) $request->get('per_page', 20);
        $sortBy = trim($request->sort_by);
        $today = (new \DateTime())->format('Y-m-d');
        $type = $request->get('type');

        $user = User::whereIn('role_id', [2, 4])->where('status', 'Active');
        if (is_numeric($id)) {
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
            'school.user:id,full_name',
        ]);

        $query->where(['tutor_id' => $user->id, 'status' => 'Approved']);
        //$query->whereDate('end_date', '>=', $today);

        if ($type === 'upcoming') {
            $query->whereDate('start_date', '>', $today);
        } elseif ($type === 'ongoing') {
            // $query->whereDate('start_date', '<=', $today)
            //       ->whereDate('end_date', '>=', $today);
        } elseif ($type === 'past') {
            $query->whereDate('end_date', '<', $today);
        }

        $classes = $query->paginate($perPage);


        $classes->getCollection()->transform(function ($class) {
            $class->formatted_start_date = formatDisplayDate($class->start_date, 'd/m/Y');
            $class->formatted_end_date = formatDisplayDate($class->end_date, 'd/m/Y');

            $class->tutor->about  = null;
            $class->tutor->avatar  = null;

            $tutorInfo = Tutor::select('about', 'avatar')->where('id', $class->tutor_id)->first();
            if (!empty($tutorInfo) && !empty($tutorInfo->about)) {
                $class->tutor->about = $tutorInfo->about;
            }
            if (!empty($tutorInfo) && !empty($tutorInfo->avatar)) {
                $class->tutor->avatar = $tutorInfo->avatar;
            }


            $duration = calculateDuration($class->start_date, $class->end_date);
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

        return jsonResponse(true, 'Tutor classes fetched successfully', [
            'classes' => $classes->items(),
            'total' => $classes->total(),
            'current_page' => $classes->currentPage(),
            'per_page' => $classes->perPage(),
            'last_page' => $classes->lastPage(),
        ]);
    }

    public function course(Request $request, $id)
    {
        $perPage = $request->get('per_page', 20);

        $user = User::whereIn('role_id', [2, 4])->where('status', 'Active');
        if (is_numeric($id)) {
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
            'user:id,full_name,user_name',
            'school:id,school_name,school_slug',
            'courseAsset',
            'reviews'
        ])
        ->where(['user_id' => $user->id, 'status' => 'Active'])
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->withCount(['enrollments as total_enrollments']);

        if($request->get('current_id')) 
        {
            $query->where('id','!=',$request->get('current_id'));
        }

        $courses = $query->paginate($perPage);

        return jsonResponse(true, 'Courses fetched successfully', [
            'courses' => $courses->items(),
            'total' => $courses->total(),
            'current_page' => $courses->currentPage(),
            'per_page' => $courses->perPage(),
            'last_page' => $courses->lastPage(),
        ]);
    }


    public function getMonthWiseSlots($id)
    {
        $user = User::whereIn('role_id', [2, 4])->where('status', 'Active');
        if (is_numeric($id)) {
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


    public function getCategories(Request $request, $id)
    {
        // Find tutor
        $userQuery = User::whereIn('role_id', [2, 4])
            ->where('status', 'Active');

        if (is_numeric($id)) {
            $userQuery->where('id', $id);
        } else {
            $userQuery->where('user_name', $id);
        }

        $tutor = $userQuery->first();

        $categories = TutorCategoryLevelFour::select('id', 'category_id')
            ->where('tutor_id', $tutor->id)
            ->with([
                'categoryLevelFour:category_id,sub_category_id,sub_sub_category_id,id,title,slug',
                'categoryLevelFour.category',
                'categoryLevelFour.categoryLevelTwo',
                'categoryLevelFour.categoryLevelThree',
            ])
            ->get();

        return jsonResponse(true, 'Categories', [
            'categories' => $categories
        ]);
    }

    public function oneOnOneSlot(Request $request, $id)
    {
        // Find tutor
        $userQuery = User::whereIn('role_id', [2, 4])
            ->where('status', 'Active');

        if (is_numeric($id)) {
            $userQuery->where('id', $id);
        } else {
            $userQuery->where('user_name', $id);
        }

        $tutor = $userQuery->first();

        if (!$tutor) {
            return jsonResponse(false, 'Tutor not found in our database.', null, 404);
        }

        $today = new \DateTime();

        $year  = $request->year  ?? $today->format('Y');
        $month = $request->month ?? $today->format('m');

        $startDate = new \DateTime("$year-$month-01");
        $endDate   = clone $startDate;
        $endDate->modify('+1 months'); 

        $slotsCollection = OneOnOneClassSlot::where('tutor_id', $tutor->id)
            ->where('is_active', 1)
            ->whereBetween('class_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('class_date')
            ->orderBy('start_time')
            ->get();
       
        $availableDates  = $slotsCollection->pluck('class_date')->unique()->values();

       

        $slotsCollection = $slotsCollection->map(function ($slot) {
            $classDateObj = new \DateTime($slot->class_date);
            $startTime    = new \DateTime($slot->start_time);
            $endTime      = new \DateTime($slot->end_time);

            $interval = $startTime->diff($endTime);

            $totalHours = ($interval->days * 24) + $interval->h + ($interval->i / 60);

            return [

                "id" => $slot->id,
                "tutor_id" => $slot->tutor_id,
                "class_date" => $slot->class_date,
                "date_key" => $classDateObj->format('d-m-Y'),

                "start_time" => $slot->start_time,
                "end_time" => $slot->end_time,

                "is_free_trial" => $slot->is_free_trial,
                "is_active" => $slot->is_active,
                "timezone" => $slot->timezone,

                "class_date_formatted" => $classDateObj->format('jS F Y'),

                "start_time_formatted" => $startTime->format('h:i A'),
                "end_time_formatted" => $endTime->format('h:i A'),

                "total_hours" => $totalHours,

                // "categories" => $categories

            ];
        })
        ->groupBy('date_key');

        return jsonResponse(true, 'Fetching slots', [
            'current_month'   => (new \DateTime("$year-$month-01"))->format('F Y'),
            'available_dates' => $availableDates,
            'slots'           => $slotsCollection
        ]);
    }


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
