<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SchoolAggrement;
use App\Models\Classes;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Course::query();

        // ------------Filter---------------
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('category_level_two_id')) {
            $query->where('sub_category_id', $request->category_level_two_id);
        }

        if ($request->filled('category_level_three_id')) {
            $query->where('sub_sub_category_id', $request->category_level_three_id);
        }

        if ($request->filled('category_level_four_id')) {
            $query->where('category_level_four_id', $request->category_level_four_id);
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // ---------- Rating Filter ----------
        if ($request->filled('rating')) {
            $query->withAvg('reviews', 'rating')
                ->having('reviews_avg_rating', '>=', $request->rating);
        }
       
        // ---------- Relationships ----------
        $query->with([
            'user',
            'category',
            'courseType',
            'subCategory',
            'subSubCategory',
            'courseOutcomes',
            'courseRequirements',
            'courseAsset',
            'courseSeo',
            'courseChapters',
            'reviews'
        ])
        ->withAvg('reviews', 'rating')
        ->withCount('reviews');

        
        // ---------- Sorting ----------
        if ($request->filled('sort_by')) {
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
                    $query->orderBy('created_at', 'desc');
                    break;
                default:
                    // fallback if an unknown sort is passed
                    $query->orderByDesc('reviews_count')
                            ->orderByDesc('reviews_avg_rating')
                            ->orderByDesc('created_at');
                    break;
            }
        } else {
            // ---------- Default sort: Most popular ----------
            $query->orderByDesc('reviews_count')
                    ->orderByDesc('reviews_avg_rating')
                    ->orderByDesc('created_at');
        }

        $perPage = $request->get('per_page', 10);
        $courses = $query->paginate($perPage);

        return jsonResponse(true, 'Course list', $courses);
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

            $totalCourse = Course::where('school_id',$course->school->id)->count();
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


            $totalCourse = Course::where('user_id',$course->user->id)->count();
            $creator['total_course'] = $totalCourse;

            $totalClasses = Classes::where('user_id',$course->user->id)->count();
            $creator['total_classes'] = 0;

            $totalTutors = Course::where('user_id',$course->user->id)->count();
            $creator['total_classes'] = $totalTutors;
        }



        $course->creator = $creator;

        $course->formatted_created_at = formatDisplayDate($course->created_at,'d/m/Y');
        $course->formatted_updated_at = formatDisplayDate($course->updated_at,'d/m/Y');

        $course->avg_rating = 4.5;

        
        $course->total_lessons = $course->courseChapters->sum('course_lessons_count');
            
        

        $data['course'] = $course;
        return jsonResponse(true, 'Course details', $data);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
