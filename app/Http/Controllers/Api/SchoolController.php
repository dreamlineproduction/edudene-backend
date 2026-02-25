<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

        $search = trim($request->search);

        $categoryIds          = $request->category_id ? explode(',', $request->category_id) : [];
        $subCategoryIds       = $request->sub_category_id  ? explode(',', $request->sub_category_id)   : [];
        $subSubCategoryIds    = $request->sub_sub_category_id  ? explode(',', $request->sub_sub_category_id)  : [];
        $levelFourCategoryIds = $request->sub_sub_sub_category_id  ? explode(',', $request->sub_sub_sub_category_id) : [];

        
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

               //$schools->where('school_name', 'LIKE', "%{$search}%"); 
            
            $schools->where(function ($q) use ($search) {
                foreach (explode(' ', $search) as $word) {
                    $q->where('school_name', 'LIKE', "%{$word}%");
                }
            });
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
                            $q->whereIn('sub_sub_sub_category_id', $levelFourCategoryIds);
                        }
                    })

                    ->orWhereHas('courses', function ($q) use (
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
                            $q->whereIn('sub_sub_sub_category_id', $levelFourCategoryIds);
                        }
                    });
                });
            });


        
        $schools = $schools->latest()->paginate(20);

        $schools->getCollection()->transform(function ($school) {
            $school->short_description = shortDescription($school->about_us, 100);
            return $school;
        });

      
        return jsonResponse(true, 'Categories fetched successfully', [
            'schools' => $schools->items(),
            'total' => $schools->total(),
            'current_page' => $schools->currentPage(),
            'per_page' => $schools->perPage(),
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

        $school->total_reviews = rand(100,1000);
        $school->avg_rating = 4.9;

        

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
        $today = (new \DateTime())->format('Y-m-d');

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
            'school.user:id,full_name,avatar',
        ]);

        $query->where(['school_id'=>$school->id,'status' => 'Approved']);
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
        return jsonResponse(true, 'Classes list', $data);
    }


    public function course(Request $request, $id)
    {
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


    public function teachers(Request $request, $id)
    {
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


        $schoolId = $school->id;

        $perPage = $request->get('per_page', 10);

        $query =  User::query();
        $query->whereIn('role_id', [4,2])        
        ->whereHas('schoolAgreements', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        });        
        $teachers = $query->paginate($perPage);

        $teachers =collect($teachers->items())->map(function ($teacher) {
            $teacher->total_reviews = 980;
            $teacher->avg_rating = 4.8;
            $teacher->hourly_rate = rand(50,250);

            $teacher->total_courses = Course::where('user_id', $teacher->id)->count();
            $teacher->total_classes = Classes::where('tutor_id', $teacher->id)->count();

            $teacher->tutor = Tutor::select('id','avatar','avatar_url')->where('user_id',$teacher->id)->first();
            return $teacher;
        });

        $data['teachers'] = $teachers;
        return jsonResponse(true, 'Teachers list', $data);
    }
}
