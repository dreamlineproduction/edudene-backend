<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DateTime;
use App\Models\Tutor;
use App\Models\User;
use App\Models\School;
use App\Models\Classes;
use App\Models\ClassSessions;
use App\Models\Course;

class SchoolController extends Controller
{
  
    public function index(Request $request)
    {
        $hasFilter = false;

        $perPage = (int) $request->get('per_page', 20);
        $search = trim($request->search);
        $sortBy = trim($request->sort_by);

        $categoryIds          = $request->category_id ? explode(',', $request->category_id) : [];
        $subCategoryIds       = $request->sub_category_id  ? explode(',', $request->sub_category_id)   : [];
        $subSubCategoryIds    = $request->sub_sub_category_id  ? explode(',', $request->sub_sub_category_id)  : [];
        $levelFourCategoryIds = $request->sub_sub_sub_category_id  ? explode(',', $request->sub_sub_sub_category_id) : [];

        $countryName        = $request->country_name ? $request->country_name : null;
        $stateName          = $request->state_name ? $request->state_name : null;

        
        if(!empty($categoryIds) || 
            !empty($subCategoryIds) || 
            !empty($subSubCategoryIds) || 
            !empty($levelFourCategoryIds)) {

            $hasFilter = true;
        }


        $schools = School::where('status', 'Active')
            ->with('user:id,full_name,email')
            ->withCount(['tutors', 'courses', 'classes']);

        if (!empty($search)) {            
            $schools->where(function ($q) use ($search) {
                foreach (explode(' ', $search) as $word) {
                    $q->where('school_name', 'LIKE', "%{$word}%");
                }
            });
        }

        if(!empty($countryName)) {
            $schools->where('country',$countryName);
        }

        if(!empty($stateName)) {
            $schools->where('state',$stateName);
        }


        $schools->when($hasFilter, function ($query) use (
                $categoryIds,
                $subCategoryIds,
                $subSubCategoryIds,
                $levelFourCategoryIds
            ) {

                $query->where(function ($query) use (
                    $categoryIds,
                    $subCategoryIds,
                    $subSubCategoryIds,
                    $levelFourCategoryIds
                ) {

                    $query->whereHas('classes', function ($q) use (
                        $categoryIds,
                        $subCategoryIds,
                        $subSubCategoryIds,
                        $levelFourCategoryIds
                    ) {

                        if ($categoryIds) {
                            $q->whereIn('category_id', $categoryIds);
                        }

                        if ($subCategoryIds) {
                            $q->whereIn('sub_category_id', $subCategoryIds);
                        }

                        if ($subSubCategoryIds) {
                            $q->whereIn('sub_sub_category_id', $subSubCategoryIds);
                        }

                        if ($levelFourCategoryIds) {
                            $q->whereIn('category_level_four_id', $levelFourCategoryIds);
                        }
                    })

                    ->orWhereHas('courses', function ($q) use (
                        $categoryIds,
                        $subCategoryIds,
                        $subSubCategoryIds,
                        $levelFourCategoryIds
                    ){

                        if ($categoryIds) {
                            $q->whereIn('category_id', $categoryIds);
                        }

                        if ($subCategoryIds) {
                            $q->whereIn('sub_category_id', $subCategoryIds);
                        }

                        if ($subSubCategoryIds) {
                            $q->whereIn('sub_sub_category_id', $subSubCategoryIds);
                        }

                        if ($levelFourCategoryIds) {
                            $q->whereIn('category_level_four_id', $levelFourCategoryIds);
                        }
                });
            });
        });
        
        // Sorting
        if($sortBy === 'name_desc') {
            $schools->orderBy('school_name','DESC');
        } else if($sortBy === 'name_asc') {
            $schools->orderBy('school_name','ASC');
        } else {
            $schools->latest();
        }

        $schools = $schools->paginate($perPage);

        $schools->getCollection()->transform(function ($school) {
            $school->short_description = shortDescription($school->about_us, 100);
            return $school;
        });

      
        return jsonResponse(true, 'Categories fetched successfully', [
            'schools' => $schools->items(),
            'total' => $schools->total(),
            'current_page' => $schools->currentPage(),
            'per_page' => $schools->perPage(),
            'last_page' => $schools->lastPage(),
        ]);
    }

    
    public function popularSchool()
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

    public function relatedSchool(Request $request)
    {
        $slug = $request->get('slug');
        $school = School::where('school_slug',$slug)->first();

        if(!$school) {
            return jsonResponse(false, 'School not found', [],404);
        }

        // $categoryIds = Classes::where('school_id', $school->id)
        //     ->pluck('category_id')
        //     ->unique()
        //     ->toArray();

        
        $relatedSchools = School::where('school_slug', '!=', $slug)
            ->where('status', 'Active')
            // ->where(function($q) use ($school) {
            //     $q->where('category_id', $school->category_id)
            //       ->orWhere('country', $school->country)
            //       ->orWhere('state', $school->state);
            // })
            ->limit(6)
            ->get();

        if ($relatedSchools->count() < 6) {
            $extra = School::where('school_slug', '!=', $slug)
                ->inRandomOrder()
                ->limit(6 - $relatedSchools->count())
                ->get();

            $relatedSchools = $relatedSchools->merge($extra);
        }

        $relatedSchools = $relatedSchools->map(function ($school) {
            $school->short_description = shortDescription($school->about_us, 100);
            return $school;
        });

        return jsonResponse(true, 'Categories fetched successfully', [
            'related_schools' => $relatedSchools,
        ]);
       
    }

    public function show(string $id)
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
        ])
        ->withCount('tutors')
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

        if (!$school) {
            return jsonResponse(false, 'School not found in our database.', null, 404);
        }

       
        

        $school->short_description = shortDescription($school->about_us, 100);
        $school->profile_created = formatDisplayDate($school->created_at, 'Y');

        $school->total_reviews = 0;
        $school->avg_rating =0;

        

        $data['schools'] = $school;
        return jsonResponse(true, 'Schools', $data);
    }


    public function viewTimelineModal(string $id)
    {
        $classes =  Classes::select(
            'start_date',
            'end_date',
            'id',
            'school_id',
            'duration',
            'price')
            ->where('id', $id)
            ->first();

        if(empty($classes)){
            return jsonResponse(false, 'Class not found in our database', null, 404);
        }

        $classes->formatted_start_date = formatDisplayDate($classes->start_date,'d-M-Y');
        $classes->formatted_end_date = formatDisplayDate($classes->end_date,'d-M-Y');

        $classes->formatted_total_hours  = minutesToHours($classes->duration);            
        $duration = calculateDuration($classes->start_date,$classes->end_date);
        if (!$duration) {
            $classes->duration = null;
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

        $classes->formatted_duration = implode(', ', $parts);
        
        $classSessions =  ClassSessions::where('class_id', $id)->get();

        if($classSessions->isEmpty()){
            return jsonResponse(false, 'No sessions found for this class', null, 404);
        }

        $classSessions = collect($classSessions)->map(function ($session) {
            $session->formatted_start_time = formatDisplayDate($session->start_time,'H:i');
            $session->formatted_end_time = formatDisplayDate($session->end_time,'H:i');
            $session->formatted_start_date = formatDisplayDate($session->start_date);

            $session->time_duration = calculateTimeDuration($session->start_time,$session->end_time);
            
            return $session;
        });

        $data['classes'] = $classes;
        $data['sessions'] = $classSessions;

        return jsonResponse(true, 'Class sessions list', $data);
    }


    public function classes(Request $request, $id)
    {
        $perPage = (int) $request->get('per_page', 20);
        $sortBy = trim($request->sort_by);
        $type = $request->get('type');

        $today = (new DateTime())->format('Y-m-d');

        $q  = School::query();

        if(is_numeric($id)){
            $q->where('id', $id);
        } else {
            $q->where('school_slug', $id);
        }

        $school = $q->where('status', 'Active')->first();
        if (!$school) {
            return jsonResponse(false, 'School not found in our database.', null, 404);
        }


        $query = Classes::query();

        $query->with([
            'class_type:id,title,status',
            'category:id,title',
            'sub_category:id,title,category_id',
            'sub_sub_category:id,title,sub_category_id',
            'category_level_four:id,title,sub_sub_category_id',
            'tutor:id,full_name,email',
            'school:id,user_id,school_name,school_slug',
            'school.user:id,full_name',
        ]);

        $query->where(['school_id'=>$school->id,'status' => 'Approved']);

        if ($type === 'upcoming') {
            $query->whereDate('start_date', '>', $today);
        } elseif ($type === 'ongoing') {
            $query->whereDate('start_date', '<=', $today)
                  ->whereDate('end_date', '>=', $today);
        } elseif ($type === 'past') {
            $query->whereDate('end_date', '<', $today);
        }

       // $query->whereDate('end_date', '>=', $today);

        if(!empty($sortBy)) {
            $query->where(['class_type_id'=>$sortBy]);
        }

        $classes = $query->paginate($perPage);

        $classes->getCollection()->transform(function ($class) {
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

        return jsonResponse(true, 'User fetched successfully', [
            'classes' => $classes->items(),
            'total' => $classes->total(),
            'current_page' => $classes->currentPage(),
            'per_page' => $classes->perPage(),
            'last_page' => $classes->lastPage(),
        ]);
    }

    public function course(Request $request, $id)
    {
        $perPage = (int) $request->get('per_page', 20);
        $sortBy = trim($request->sort_by);

        $q  = School::query();

        if(is_numeric($id)){
            $q->where('id', $id);
        } else {
            $q->where('school_slug', $id);
        }

        $school = $q->where('status', 'Active')->first();
        if (!$school) {
            return jsonResponse(false, 'School not found in our database.', null, 404);
        }


        $query = Course::query();
        $query->with([
            'user:id,full_name',
            'school:id,school_name',           
            'courseAsset',
            'reviews'
        ])
        ->where(['school_id'=>$school->id,'status' => 'Active'])
        ->withAvg('reviews', 'rating')
        ->withCount('reviews');

        if(!empty($sortBy)) {
            $query->orderBy('title',$sortBy);
        }

        $courses = $query->paginate($perPage);

        // $courses = collect($courses->items())->map(function ($course) {
        $courses->getCollection()->transform(function ($course) {
            $course->enrollment_count = 0;            
            return $course;
        });

        return jsonResponse(true, 'User fetched successfully', [
            'courses' => $courses->items(),
            'total' => $courses->total(),
            'current_page' => $courses->currentPage(),
            'per_page' => $courses->perPage(),
            'last_page' => $courses->lastPage(),
        ]);
    }


    public function teachers(Request $request, $id)
    {
        $perPage = (int) $request->get('per_page', 20);
        $sortBy = trim($request->sort_by);

        $schoolQuery  = School::query();
        if(is_numeric($id)){
            $schoolQuery->where('id', $id);
        } else {
            $schoolQuery->where('school_slug', $id);
        }

        $school = $schoolQuery->where('status', 'Active')->first();
        if (!$school) {
            return jsonResponse(false, 'School not found in our database.', null, 404);
        }


        $schoolId = $school->id;

        $query =  User::query()
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
            ->whereIn('role_id', [4,2])
            ->leftJoin('tutors', 'users.id', '=', 'tutors.user_id')
            ->whereHas('schoolAgreements', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            });  

        if(!empty($sortBy)) {
            $query->orderBy('users.full_name',$sortBy);
        }

        $teachers = $query->paginate($perPage);

        $teachers->getCollection()->transform(function ($teacher) {
            $teacher->formatted_created_at = $teacher->created_at 
                ? formatDisplayDate($teacher->created_at, 'd-M-Y H:i:A') 
                : null;
            $teacher->total_courses = Course::where('user_id', $teacher->id)->count();
            $teacher->total_classes = Classes::where('tutor_id', $teacher->id)->count(); 

            return $teacher;           
        });

        return jsonResponse(true, 'User fetched successfully', [
            'teachers' => $teachers->items(),
            'total' => $teachers->total(),
            'current_page' => $teachers->currentPage(),
            'per_page' => $teachers->perPage(),
            'last_page' => $teachers->lastPage(),
        ]);
    }
}
