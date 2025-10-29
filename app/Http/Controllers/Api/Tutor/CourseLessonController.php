<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseChapter;
use App\Models\CourseLesson;
use Illuminate\Http\Request;

class CourseLessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $courseId, string $courseChapterId)
    {
        //
        $data =  CourseLesson::where(['course_id'=>$courseId,'course_chapter_id'=>$courseChapterId])->get();

        return jsonResponse(true, 'Course lessons retrieved successfully.', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $courseId, string $courseChapterId)
    {
        
        $course =  Course::find($courseId);
        $courseChaper =  CourseChapter::find($courseChapterId);

        if (!$course || !$courseChaper || $courseChaper->course_id != $course->id) {
            return jsonResponse(false, 'Course or Course chapter not found in our database.', null, 404);
        }

        //'Youtube','Vimeo','Video','Image','Document','VideoUrl'

        $validation =[
            'type' => 'required|in:Youtube,Vimeo,VideoUrl,Video,Image,Document',
        ];

        if($request->type == 'Youtube' || $request->type == 'Vimeo' || $request->type == 'VideoUrl'){
            $validation['video_url'] = 'required|url|max:255';
        }elseif($request->type == 'Video'){
            $validation['video'] = 'required|file|mimes:mp4,mov,avi,wmv|max:20480'; //  20MB
        }elseif($request->type == 'Image'){
            $validation['image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:5120'; // max 5MB     
        } elseif($request->type == 'Document'){
            $validation['document'] = 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240'; // max 10MB     
        }



        $request->validate($validation);

        $request->merge([
            'course_chapter_id' => $courseChaper->id,
            'course_id' => $course->id,
        ]);
        $data = CourseLesson::create($request->toArray());

        return jsonResponse(true, 'Course lesson created successfully.', $data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $courseId, string $courseChapterId, string $id)
    {
        //
        $course =  Course::find($courseId);
        $courseChaper =  CourseChapter::find($courseChapterId);

        if (!$course || !$courseChaper || $courseChaper->course_id != $course->id) {
            return jsonResponse(false, 'Course or Course chapter not found in our database.', null, 404);
        }

        $data = CourseLesson::where(['course_id' => $courseId, 'course_chapter_id' => $courseChapterId, 'id' => $id])->first();

        if (!$data) {
            return jsonResponse(false, 'Course lesson not found.', null, 404);
        }

        return jsonResponse(true, 'Course lesson details retrieved successfully.', $data);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $courseId, string $courseChapterId, string $id)
    {
        $course =  Course::find($courseId);
        $courseChaper =  CourseChapter::find($courseChapterId);

        if (!$course || !$courseChaper || $courseChaper->course_id != $course->id) {
            return jsonResponse(false, 'Course or Course chapter not found in our database.', null, 404);
        }

        //'Youtube','Vimeo','Video','Image','Document','VideoUrl'
        $validation =[
            'type' => 'required|in:Youtube,Vimeo,VideoUrl,Video,Image,Document',
        ];

        if($request->type == 'Youtube' || $request->type == 'Vimeo' || $request->type == 'VideoUrl'){
            $validation['video_url'] = 'required|url|max:255';
        }elseif($request->type == 'Video'){
            $validation['video'] = 'required|file|mimes:mp4,mov,avi,wmv|max:20480'; //  20MB
        }elseif($request->type == 'Image'){
            $validation['image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:5120'; // max 5MB     
        } elseif($request->type == 'Document'){
            $validation['document'] = 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240'; // max 10MB     
        }

        


        $request->validate($validation);

        $courseLesson =  CourseLesson::where(['course_id' => $courseId, 'course_chapter_id' => $courseChapterId, 'id' => $id])->first();
        if (!$courseLesson) {
            return jsonResponse(false, 'Course lesson not found.', null, 404);
        }

        $request->merge([
            'course_chapter_id' => $courseChaper->id,
        ]);
        $courseLesson->update($request->toArray());

        
        $request->merge([
            'course_chapter_id' => $courseChaper->id,
            'course_id' => $course->id,
        ]);
        $data = CourseLesson::create($request->toArray());

        return jsonResponse(true, 'Course lesson created successfully.', $data);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $courseId, string $courseChapterId, string $id)
    {
        //
        $course =  Course::find($courseId);
        $courseChaper =  CourseChapter::find($courseChapterId);

        if (!$course || !$courseChaper || $courseChaper->course_id != $course->id) {
            return jsonResponse(false, 'Course or Course chapter not found in our database.', null, 404);
        }


        // Delete Course Lesson
        $courseLesson = CourseLesson::find($id);
        if (!$courseLesson) {
            return jsonResponse(false, 'Course lesson not found.', null, 404);
        }

        $courseLesson->delete();
        return jsonResponse(true, 'Course lesson deleted successfully.',);
    }
}
