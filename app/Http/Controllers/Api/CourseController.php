<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\SchoolAggrement;
use App\Models\Classes;
use App\Models\CourseLesson;
use App\Models\SchoolTheme;
use App\Models\CourseBulkDiscount;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $search = trim($request->search);
        $sortBy = trim($request->sort_by);

        $categoryIds          = $request->category_id ? explode(',', $request->category_id) : [];
        $subCategoryIds       = $request->sub_category_id ? explode(',', $request->sub_category_id) : [];
        $subSubCategoryIds    = $request->sub_sub_category_id ? explode(',', $request->sub_sub_category_id) : [];
        $levelFourCategoryIds = $request->sub_sub_sub_category_id ? explode(',', $request->sub_sub_sub_category_id) : [];

        $query = Course::query();

       

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                foreach (explode(' ', $search) as $word) {
                    $q->where('title', 'LIKE', "%{$word}%");
                }
            });
        }


        if ($request->filled('rating')) {
            $query->whereHas('reviews', function ($q) use ($request) {
                $q->havingRaw('AVG(rating) >= ?', [$request->rating]);
            });
        }

        if ($request->filled('start_price')) {
            $query->whereRaw('COALESCE(discount_price, price) >= ?', [$request->start_price]);
        }

        if ($request->filled('end_price')) {
            $query->whereRaw('COALESCE(discount_price, price) <= ?', [$request->end_price]);
        }

        if (!empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        if (!empty($subCategoryIds)) {
            $query->whereIn('sub_category_id', $subCategoryIds);
        }

        if (!empty($subSubCategoryIds)) {
            $query->whereIn('sub_sub_category_id', $subSubCategoryIds);
        }

        if (!empty($levelFourCategoryIds)) {
            $query->whereIn('category_level_four_id', $levelFourCategoryIds);
        }

        if ($request->filled('language')) {
            $query->where('language_id', $request->language);
        }
        
        $query->with([
            'user:id,full_name,user_name',
            'school:id,school_name,school_slug',
            'courseAsset',
            'reviews'
        ])
        ->where('status','Active')
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->withCount(['enrollments as total_enrollments']);

        // Sorting
        if ($sortBy === 'name_desc') {
            $query->orderBy('courses.title', 'DESC');
        } elseif ($sortBy === 'name_asc') {
            $query->orderBy('courses.title', 'ASC');
        } else {
            $query->orderBy('courses.created_at', 'DESC');
        }

        
        $courses = $query->paginate($perPage);

        $courses->getCollection()->transform(function ($course) {
            $course->avg_rating = 0.0; 
            $course->review_count = 0; 
            $course->enrollment_count = 0; 
            return $course; 
        });

        return jsonResponse(true, 'Course list', [
            'courses' => $courses->items(),
            'total' => $courses->total(),
            'current_page' => $courses->currentPage(),
            'per_page' => $courses->perPage(),
            'last_page' => $courses->lastPage(),
        ]);
    }


    public function popularCourse(Request $request)
    {
        $perPage              =     $request->get('per_page', 10);
        $categoryIds          =     $request->category_id ? explode(',', $request->category_id) : [];
        $subCategoryIds       =     $request->sub_category_id ? explode(',', $request->sub_category_id) : [];

        $query = Course::query();

        $query->with([
            'user:id,full_name,user_name',
            'school:id,school_name,school_slug',
            'courseAsset',
            'reviews'
        ])
        ->where('status','Active')
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->withCount(['enrollments as total_enrollments']);


        // 
        if (!empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        if (!empty($subCategoryIds)) {
            $query->whereIn('sub_category_id', $subCategoryIds);
        }

        
        $query->orderByDesc('reviews_count')
                    ->orderByDesc('reviews_avg_rating')
                    ->orderByDesc('created_at');


        $paginated = $query->paginate($perPage);

        $courses = collect($paginated->items())->map(function ($course) {
            $course->avg_rating = 0.0;
            $course->review_count = 0;            
            return $course;
        });



        return jsonResponse(true, 'Popular Course list', [
            'courses' => $courses,
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $slug)
    {        
        $query = Course::query();

        $query->with([
            'user:id,full_name,user_name',
            'user.tutor:id,avatar,user_id,avatar_url,about',
            'courseType',
            'category:id,title',
            'subCategory:id,title',
            'subSubCategory:id,title',
            'categoryLevelFour:id,title',
            'courseOutcomes:id,course_id,title',
            'courseRequirements:id,course_id,title',
            'courseAsset',
            'courseSeo',
            'language:id,title',
            'courseChapters' => function ($q) {
                $q->whereHas('courseLessons') 
                  ->withCount('courseLessons')
                  ->with('courseLessons');
            },                
            'enrollments' => function ($q) {
                $q->select('id','course_id','user_id')
                ->latest()
                ->take(5)
                ->with([
                  'user:id,full_name',
                  'user.information:avatar,user_id,avatar_url'
                ]);
            },
            'reviews'
        ])
        ->with([
            'school:id,school_name,school_slug,about_us',
            'school.theme:school_id,logo_image,logo_image_url as logo_image_url',        
        ])
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->withCount('courseChapters')
        ->withCount(['enrollments as total_enrollments']);

        $course = $query->where('slug', $slug)->first();


        if(!$course){
            return jsonResponse(false, 'Course not available', null, 404);
        }

        if ($course->status !== 'Active') {
            if ($request->get('preview_token') !== $course->preview_token) {
                return jsonResponse(false, 'Course not available',null,404);
            }
        }

        // Calculation total time
        $totalSeconds = CourseLesson::where('course_id', $course->id)->sum('duration');
        $seconds = (int) $totalSeconds;

        $hours = @floor($seconds / 3600);
        $minutes = @floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            $formatted = "{$hours}h {$minutes}m";
        } else {
            $formatted = "{$minutes}m {$secs}s";
        }

        $course->total_duration = $formatted;

        $packages = [];
        if($course->school_id > 0)
        {
            $theme = SchoolTheme::select('logo_image','logo_image_url')
                ->where('school_id',$course->school->id)
                ->first();

            if($theme && !empty($theme->logo_image)) 
            {
                $creator['image_url'] = $theme->logo_image_url;
            } else {
                $creator['image_url'] = null;
            }


            $creator['id'] = $course->school->id;
            $creator['name'] = $course->school->school_name;
            
            $creator['slug'] = $course->school->school_slug;
            $creator['about_us'] = shortDescription($course->school->about_us,90);

            $totalCourse = Course::where(['school_id'=>$course->school->id,'status'=>'Active'])->count();
            $creator['total_course'] = $totalCourse;

            $totalClasses = Classes::where('school_id',$course->school->id)->count();
            $creator['total_classes'] = $totalClasses;

            $totalTutors = SchoolAggrement::where('school_id',$course->school->id)->count();
            $creator['total_tutors'] = $totalTutors;


            // Select Schools Packages
            $packages = CourseBulkDiscount::select('id','owner_id','title',
                'owner_type','min_quantity','discount_percentage','text'
                )->where([
                    'owner_id'=>$course->school->id,
                    'owner_type'=>'school',
                    'status' => 'Active'
                ])
                ->where('min_quantity', '<=', $totalCourse)
                ->orderBy('min_quantity', 'desc')
                ->get();
        } else {
            $creator['id'] = $course->user->id;
            $creator['name'] = $course->user->full_name;
            $creator['image_url'] = $course->user->tutor->avatar_url;
            $creator['slug'] = $course->user->user_name;

            $creator['about_us'] = shortDescription($course->user->tutor->about,90);


            $totalCourse = Course::where(['user_id'=>$course->user->id,'status'=>'Active'])->count();
            $creator['total_course'] = $totalCourse;

            $totalClasses = Classes::where('tutor_id',$course->user->id)->count();
            $creator['total_classes'] = 0;

            //$totalTutors = Course::where('user_id',$course->user->id)->count();
            $creator['total_tutors'] = 0;

            // Select Schools Packages
           $packages = CourseBulkDiscount::select('id','owner_id','title',
                'owner_type','min_quantity','discount_percentage','text'
                )->where([
                    'owner_id'=>$course->user->id,
                    'owner_type'=>'tutor',
                    'status' => 'Active'
                ])
                ->where('min_quantity', '<=', $totalCourse)
                ->orderBy('min_quantity', 'desc')
                ->get();
        }



        $course->creator = $creator;

        $course->formatted_created_at = formatDisplayDate($course->created_at,'d/m/Y');
        $course->formatted_updated_at = formatDisplayDate($course->updated_at,'d/m/Y');

        $course->avg_rating = "0.0";        
        $course->total_lessons = $course->courseChapters->sum('course_lessons_count');
            
        

        return jsonResponse(true, 'Course details',[
            'course' => $course,
            'packages' => $packages,
        ]);
    }


    public function freeLesson(string $slug)
    {
        $course = Course::where('slug', $slug)->first();

        if (!$course) {
            return jsonResponse(false, 'Course not found');
        }

        $courseLessons = CourseLesson::select(
                'id','title','duration','type','video_url','video','image','image_url'
            )
            ->where([
                'course_id' => $course->id,
                'is_free_lesson' => 'yes'
            ])
            ->get()
            ->transform(function ($lesson) {
                if($lesson->duration) {
                    $lesson->duration_formatted = gmdate("H:i:s", $lesson->duration);    
                } else {
                    $lesson->duration_formatted = null;    
                }
                
                return $lesson;
            });

        return jsonResponse(true, 'Free lessons list', [
            'free_lessons' => $courseLessons,
        ]);
    }


    public function relatedCourse(Request $request,$slug) 
    {
        $course = Course::where('slug', $slug)->first();


        if (!$course) {
            return jsonResponse(false, 'Course not found');
        }

        $relatedCourses = Course::query()
            ->where('status', 'Active')
            ->where('id', '!=', $course->id)

            // ->where(function ($q) use ($course) {
            //     $q->where('category_id', $course->category_id)
            //       ->orWhere('sub_category_id', $course->sub_category_id)
            //       ->orWhere('sub_sub_category_id', $course->sub_sub_category_id);
            // })

            ->with([
                'user:id,full_name,user_name',
                'school:id,school_name,school_slug',
                'courseAsset'
            ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->withCount(['enrollments as total_enrollments'])
            ->latest()
            ->take(6)
            ->get();

        return jsonResponse(true, 'Related courses', [
            'related_courses' => $relatedCourses
        ]);
    }

}
