<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseChapter;
use Illuminate\Http\Request;

class CourseChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $courseId)
    {
        //
        $data = CourseChapter::where(['course_id'=>$courseId])->get();
        return jsonResponse(true, 'Course chapters retrieved successfully.', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $courseId)
    {
        //
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
        ]);

        $course =  Course::find($courseId);
        if (!$course) {
            return jsonResponse(false, 'Course not found in our database.', null, 404);
        }


        // Create Course Chapter
        $request->merge(['course_id' => $courseId]);
        $data = CourseChapter::create($request->toArray());

        return jsonResponse(true, 'Course chapter created successfully.', $data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $couseId, string $id)
    {
        //
        $data = CourseChapter::where(['course_id' => $couseId, 'id' => $id])->first();
        if (!$data) {
            return jsonResponse(false, 'Course chapter not found.', null, 404);
        }

        return jsonResponse(true, 'Course chapter details retrieved successfully.', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $couseId, string $id)
    {
        //
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
        ]);

        $courseChapter = CourseChapter::where(['course_id' => $couseId, 'id' => $id])->first();
        if (!$courseChapter) {
            return jsonResponse(false, 'Course chapter not found.', null, 404);
        }
        $courseChapter->update($request->toArray());

        return jsonResponse(true, 'Course chapter updated successfully.', $courseChapter);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $couseId, string $id)
    {
        //
        $courseChapter = CourseChapter::where(['course_id' => $couseId, 'id' => $id])->first();
        if (!$courseChapter) {
            return jsonResponse(false, 'Course chapter not found.', null, 404);
        }
        $courseChapter->delete();

        return jsonResponse(true, 'Course chapter deleted successfully.',);
    }
}
