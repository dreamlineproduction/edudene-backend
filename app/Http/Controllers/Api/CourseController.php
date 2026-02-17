<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolAggrement;
use App\Models\Classes;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Course::query();

        $categoryIds          = $request->category_id ? explode(',', $request->category_id) : [];
        $subCategoryIds       = $request->sub_category_id ? explode(',', $request->sub_category_id) : [];
        $subSubCategoryIds    = $request->sub_sub_category_id ? explode(',', $request->sub_sub_category_id) : [];
        $levelFourCategoryIds = $request->sub_sub_sub_category_id ? explode(',', $request->sub_sub_sub_category_id) : [];

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
            $query->whereIn('sub_sub_sub_category_id', $levelFourCategoryIds);
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        // ---------- SEARCH ----------
        $search = trim($request->search);
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                foreach (explode(' ', $search) as $word) {
                    $q->where('title', 'LIKE', "%{$word}%");
                }
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // ---------- RATING ----------
        if ($request->filled('rating')) {
            $query->whereHas('reviews', function ($q) use ($request) {
                $q->havingRaw('AVG(rating) >= ?', [$request->rating]);
            });
        }

        // ---------- RELATIONSHIPS ----------
        $query->with([
            'user:id,full_name',
            'school:id,school_name',
            'courseAsset',
            'reviews'
        ])
        ->withAvg('reviews', 'rating')
        ->withCount('reviews');

        // ---------- SORT ----------
        switch ($request->sort_by) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;

            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;

            case 'rating_desc':
                $query->orderBy('reviews_avg_rating', 'desc');
                break;

            case 'latest':
                $query->latest();
                break;

            default:
                $query->orderByDesc('reviews_count')
                      ->orderByDesc('reviews_avg_rating')
                      ->orderByDesc('created_at');
                break;
        }

        $perPage = $request->get('per_page', 10);
        $paginated = $query->paginate($perPage);

        $courses = collect($paginated->items())->map(function ($course) { 
            $course->avg_rating = 4.5; 
            $course->review_count = rand(1,5); 
            $course->enrollment_count = rand(100,500); 
            return $course; 
        });

        return jsonResponse(true, 'Course list', [
            'courses' => $courses,
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }


    public function popularCourse(Request $request)
    {
        $perPage = $request->get('per_page', 10);
       

        $query = Course::query();

        // ---------- Relationships ----------
        $query->with([
            'user:id,full_name',
            'school:id,school_name',
            'courseAsset',
            'reviews'
        ])
        ->withAvg('reviews', 'rating')
        ->withCount('reviews');

        
        $query->orderByDesc('reviews_count')
                    ->orderByDesc('reviews_avg_rating')
                    ->orderByDesc('created_at');


        $paginated = $query->paginate($perPage);

        $courses = collect($paginated->items())->map(function ($course) {
            $course->avg_rating = 4.5;
            $course->review_count = rand(1,5);
            $course->enrollment_count = rand(100,500);

            
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
    public function show(string $slug)
    {
        $query = Course::query();

        // ---------- Relationships ----------
        $query->with([
            'user:id,full_name,user_name',
            'user.information:id,user_id,avatar,avatar_url',
            'school:id,school_name,logo,logo_url,about_us,school_slug',            
            'courseType',
            'category:id,title',
            'subCategory:id,title',
            'subSubCategory:id,title',
            'categoryLevelFour:id,title',
            'courseOutcomes:id,course_id,title',
            'courseRequirements:id,course_id,title',
            'courseAsset',
            'courseSeo',
            'courseChapters.courseLessons',
            'courseChapters' => function ($q) {
                $q->withCount('courseLessons');
            },                        
            'reviews'
        ])
       
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->withCount('courseChapters');

        $course = $query->where('slug', $slug)->first();

        if(!$course){
            return jsonResponse(false, 'Course not found', null, 404);
        }

        if($course->school_id > 0)
        {
            $creator['id'] = $course->school->id;
            $creator['name'] = $course->school->school_name;
            $creator['image_url'] = $course->school->logo_url;
            $creator['slug'] = $course->school->school_slug;
            $creator['about_us'] = shortDescription($course->school->about_us,90);

            $totalCourse = Course::where(['school_id'=>$course->school->id,'status'=>'Active'])->count();
            $creator['total_course'] = $totalCourse;

            $totalClasses = Classes::where('school_id',$course->school->id)->count();
            $creator['total_classes'] = $totalClasses;

            $totalTutors = SchoolAggrement::where('school_id',$course->school->id)->count();
            $creator['total_tutors'] = $totalTutors;

        } else {
            $creator['id'] = $course->user->id;
            $creator['name'] = $course->user->full_name;
            $creator['image_url'] = $course->user->avatar_url;
            $creator['slug'] = $course->user->user_name;

            $creator['about_us'] = $course->user->about_us;


            $totalCourse = Course::where(['user_id'=>$course->user->id,'status'=>'Active'])->count();
            $creator['total_course'] = $totalCourse;

            //$totalClasses = Classes::where('user_id',$course->user->id)->count();
            $creator['total_classes'] = 0;

            //$totalTutors = Course::where('user_id',$course->user->id)->count();
            $creator['total_classes'] = 0;
        }



        $course->creator = $creator;

        $course->formatted_created_at = formatDisplayDate($course->created_at,'d/m/Y');
        $course->formatted_updated_at = formatDisplayDate($course->updated_at,'d/m/Y');

        $course->avg_rating = 4.5;

        
        $course->total_lessons = $course->courseChapters->sum('course_lessons_count');
            
        

        $data['course'] = $course;
        return jsonResponse(true, 'Course details', $data);

    }


}
