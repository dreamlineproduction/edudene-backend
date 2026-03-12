<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseEditRequestController extends Controller
{
    //

    public function sendEditRequest(Request $request, $id)
    {
        $course = Course::where(['id' => $id,'status'=> 'Active'])->first();

        if(!$course) {
            return jsonResponse(true,'Course not found in our database.',404);
        }

        $course->is_edit = $request->type;
        $course->save();

        return jsonResponse(true,'Course request updated successfully',200);
    }    
}
