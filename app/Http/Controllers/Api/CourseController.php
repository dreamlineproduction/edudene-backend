<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
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

        $course = $query->where('slug', $slug)->first();

        if(!$course){
            return jsonResponse(false, 'Course not found', null, 404);
        }

        return jsonResponse(true, 'Course details', $course);

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
