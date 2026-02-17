<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Tutor;
use App\Models\Classes;
use App\Models\CategoryLevelFour;
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
