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
            //'type' => 'required|in:Youtube,Vimeo,VideoUrl,Video,Image,Document',
			'type' => 'required|in:Youtube,Video',
        ];

        
        if($request->type == 'Youtube' || $request->type == 'Vimeo' || $request->type == 'VideoUrl'){
            $validation['video_url'] = 'required|url|max:255';
        } 
        if($request->type == 'Video' || $request->type == 'Document'){
            $validation['file_id'] = 'required|exists:files,id';
        } 

        $request->validate($validation);

        if($request->type === 'Youtube' && !isYouTube($request->video_url)){
            return jsonResponse(false,'Enter valid youtube url',null,422);
        }

        if($request->type === 'Vimeo' && !isVimeo($request->video_url)){
            return jsonResponse(false,'Enter valid vimeo url',null,422);
        }

        $request->merge([
            'course_chapter_id' => $courseChaper->id,
            'course_id' => $course->id,
        ]);

		$insertData['summary'] = $request->summary;
		$insertData['title'] = $request->title;
        $insertData['course_id'] = $courseId;
        $insertData['course_chapter_id'] = $courseChapterId;
        $insertData['type'] = $request->type;
        
        $newPath = 'courses/course-'.$courseId;

        if($request->type == 'Youtube' || $request->type == 'Vimeo' || $request->type == 'VideoUrl'){
            $insertData['video_url'] = $request->video_url;
        }

        if($request->type == 'Document' || $request->type == 'Image'){
            $finalize = finalizeFile($request->file_id,$newPath);
            $insertData['image'] = $finalize['path'];
            $insertData['image_url'] = $finalize['url'];
        }

        if($request->type == 'Video'){
            $finalizeVideo = finalizeFile($request->file_id,$newPath);
            $insertData['video'] = $finalizeVideo['video_path'];
            $insertData['video_url'] = $finalizeVideo['video_url'];
            $insertData['image'] = $finalizeVideo['poster_path'];
            $insertData['image_url'] = $finalizeVideo['poster_url'];
        }

        $data = CourseLesson::create($insertData);

        return jsonResponse(true, 'Course lesson created successfully.', ['lesson' => $data]);
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
        } 
        if($request->type == 'Video' || $request->type == 'Document'){
            $validation['file_id'] = 'required|exists:files,id';
        } 

        $request->validate($validation);

        $courseLesson =  CourseLesson::where(['course_id' => $courseId, 'course_chapter_id' => $courseChapterId, 'id' => $id])->first();
        if (!$courseLesson) {
            return jsonResponse(false, 'Course lesson not found.', null, 404);
        }

		$updateData['summary'] = $request->summary;
        $updateData['course_id'] = $courseId;
        $updateData['course_chapter_id'] = $courseChaper->id;
        $updateData['type'] = $request->type;

        $newPath = 'courses/course-'.$courseId;

        if($request->type == 'Youtube' || $request->type == 'Vimeo' || $request->type == 'VideoUrl'){
            $updateData['video_url'] = $request->video_url;
        }

        if($request->type == 'Document' || $request->type == 'Image'){
            // Delete Old Document or image
            if(notEmpty($courseLesson->image)){
                deleteS3File($courseLesson->image);
            }

            $finalize = finalizeFile($request->file_id,$newPath);
            $updateData['image'] = $finalize['path'];
            $updateData['image_url'] = $finalize['url'];
        }

         if($request->type == 'Video'){

            // Delete Old Video and poster
            if(notEmpty($courseLesson->video) && notEmpty($courseLesson->image)){
                deleteS3File($courseLesson->video);
                deleteS3File($courseLesson->image);
            }

            $finalizeVideo = finalizeFile($request->file_id,$newPath);
            $updateData['video'] = $finalizeVideo['video_path'];
            $updateData['video_url'] = $finalizeVideo['video_url'];
            $updateData['image'] = $finalizeVideo['poster_path'];
            $updateData['image_url'] = $finalizeVideo['poster_url'];
        }

        $courseLesson = $courseLesson->update($updateData);
        return jsonResponse(true, 'Course lesson updated successfully.', $updateData);

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

        // Delete video on s3
        if(($courseLesson->type === 'Document' || $courseLesson->type === 'Image') && notEmpty($courseLesson->image)){
            deleteS3File($courseLesson->image);
        }

        // Delete video on s3
        if($courseLesson->type === 'Video' && notEmpty($courseLesson->video) && notEmpty($courseLesson->image)){
            deleteS3File($courseLesson->video);
            deleteS3File($courseLesson->image);
        }
        

        $courseLesson->delete();
        return jsonResponse(true, 'Course lesson deleted successfully.',);
    }
}
