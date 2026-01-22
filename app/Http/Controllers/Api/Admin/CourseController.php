<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Mail\User\CourseDeclineMail;
use App\Mail\User\CourseInactiveMail;

use Illuminate\Support\Facades\Mail;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        //
        $courses = Course::query()
        ->with([
            'user:id,full_name,email',
            'category',
            'courseType',
            'subCategory',
            'subSubCategory',
            'courseOutcomes',
            'courseRequirements',
            'courseSeo',
            'courseChapters',
            'courseAsset'
        ]);

		if (!empty($request->search)) {
            $search = $request->search;

            $courses->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($user) use ($search) {
                       $user->where('full_name', 'like', "%{$search}%");
                       $user->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

		$sortBy = $request->get('sort_by', 'title');
    	$sortDirection = $request->get('sort_direction', 'asc');

		if (in_array($sortBy, ['id', 'title', 'full_name', 'email','created_at'])) {
			$courses = $courses->orderBy($sortBy, $sortDirection);
		} else {
			$courses = $courses->orderBy('title', 'asc');
		}

		$perPage = (int) $request->get('per_page', 10);
    	$page = (int) $request->get('page', 1);

		$paginated = $courses->paginate($perPage, ['*'], 'page', $page);

        $courses = collect($paginated->items())->map(function ($course) {
             $course->formatted_created_at = $course->created_at
                ? formatDisplayDate($course->created_at, 'd-M-Y H:i:A')
                : null;
            
            return $course;
        });

		return jsonResponse(true, 'User fetched successfully', [
			'courses' => $courses,
			'total' => $paginated->total(),
			'current_page' => $paginated->currentPage(),
			'per_page' => $paginated->perPage(),
		]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        $course = Course::with(['courseAsset','courseLessons'])->find($id);

        if (empty($course)) {
            return jsonResponse(false, 'Course not found in our database', null, 404);
        }

        // Delete Course cover media file
        if(!empty($course->courseAsset->poster)){
            deleteS3File($course->courseAsset->poster);    
        }

        if($course->courseLessons->isNotEmpty()){
            foreach ($course->courseLessons as $key => $lesson) {
                if(!empty($lesson->image)){
                    deleteS3File($lesson->image);    
                }
                if(!empty($lesson->video)){
                    deleteS3File($lesson->video);    
                }
            }
        }

        $course->delete();
        return jsonResponse(true, 'Course deleted successfully');
    }


    public function changeStatus(Request $request, $id){
        $validation = [
            'status' => 'required|in:Active,Inactive,Pending,Decline', 
        ];

        if($request->status === 'Inactive'){
            $validation['description'] = 'required|string';
        }
        if($request->status === 'Decline'){
            $validation['reason'] = 'required|string';
        }

        $request->validate($validation);

        $course = Course::find($id);

        if (empty($course)) {
            return jsonResponse(false, 'Course not found in our database', null, 404);
        }

        $course->status = $request->status;
        $course->save();


        // Send mail to user course de
        if($request->status === 'Decline')
        {
            $mailData = [
                'fullName' => $course->user->full_name,
                'reason' => $request->reason
            ];
            Mail::to($course->user->email)->send(
                new CourseDeclineMail($mailData)
            );

            $course->reason = $request->reason;
            $course->save();
        }

        // Send mail to user 
        if($request->status === 'Inactive')
        {
            $mailData = [
                'fullName' => $course->user->full_name,
                'reason' => $request->description
            ];
            
            Mail::to($course->user->email)->send(
                new CourseInactiveMail($mailData)
            );
            
            $course->reason = $request->reason;
            $course->save();
        }


        return jsonResponse(true, 'Status changed successfully');
    }
}
