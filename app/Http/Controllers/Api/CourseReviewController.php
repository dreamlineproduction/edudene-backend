<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\Request;

class CourseReviewController extends Controller
{
    //


    public function index(Request $request,$courseId)
    {
        //
        $course = Course::find($courseId);

        if(!$course){
            return jsonResponse(false, 'Course not found in our database.', null, 404);
        }


        $data = CourseReview::where('course_id', $courseId)->with('user')->get();

        return jsonResponse(true, 'Review list', $data);
    }

    public function store(Request $request,$courseId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
        ]);

        $user = auth('sanctum')->user();


        $find = ['course_id'=> $courseId ,'user_id'=> $user->id];

        $review = CourseReview::updateOrCreate($find,$request->toArray());
        return jsonResponse(true, 'Your rating has been submitted successfully', $review);
    }
}
