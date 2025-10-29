<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseOutcome;
use App\Models\CourseRequirement;
use App\Models\CourseSeo;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $user = auth('sanctum')->user();
        $data = Course::where('user_id', $user->id)
            ->with(['user',
                'courseType',
                'category',
                'subCategory',
                'subSubCategory',
                'courseOutcomes',
                'courseRequirements',
                'courseSeo',
                'courseChapters'
            ])->get();
        return jsonResponse(true, 'Course list', $data);
    }

    /**
     * Save course basic information
     */
    public function saveBasicInformation(Request $request)
    {
        //
        $validation =[
            'title' => 'required|string|max:190',
            'short_description' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|string|in:Beginner,Advanced,Intermediate',
            'course_type_id' => 'required|integer|exists:course_types,id',
            'category_id' => 'required|integer|exists:categories,id',
            'subcategory_id' => 'nullable|integer|exists:sub_categories,id',
            'sub_sub_category_id' => 'nullable|integer|exists:sub_sub_categories,id',
        ];

        if($request->has('type') && $request->type == 1){
            $validation['country_id'] = 'required|integer|exists:countries,id';
            $validation['state_id'] = 'required|integer|exists:states,id';
        }

        $request->validate($validation);

        $user = auth('sanctum')->user();
        $request->merge([
            'user_id' => $user->id,
            'status' => 'Draft',
            'slug' => generateUniqueSlug($request->title, 'App\Models\Course'),
        ]);

        $find = ['user_id' => $user->id, 'id' => $request->course_id];
        $course = Course::updateOrCreate($find,$request->toArray());
        
        return jsonResponse(true, 'Course created successfully.', $course);
    }

    /**
     * Save course requirements
     */
    public function saveRequirment(Request $request)
    {
        //
        $validation =[     
            'course_id' => 'required|integer|exists:courses,id',     
            'requirments' => 'required|array|min:1',
            'requirments.*.title' => 'required|string|max:150',
        ];
        

        $request->validate($validation);

        if(empty($request->requirments)){
            return jsonResponse(false, 'Please provide at least one requirement.', null, 422);
        }

        // Delete existing requirements and create new ones
        CourseRequirement::where('course_id', $request->course_id)->delete();

        foreach($request->requirments as $requirement){
            CourseRequirement::create([
                'course_id' => $request->course_id,
                'title' => $requirement['title'],
            ]);
        }
        
        $data =  CourseRequirement::where('course_id', $request->course_id)->get();            
        return jsonResponse(true, 'Requirments created', $data);
    }

    /**
     * Save course requirements
     */
    public function saveOutcome(Request $request)
    {
        $request->validate([     
            'course_id' => 'required|integer|exists:courses,id',     
            'outcomes' => 'required|array|min:1',
            'outcomes.*.title' => 'required|string|max:150',
        ]);

        if(empty($request->outcomes)){
            return jsonResponse(false, 'Please provide at least one outcome.', null, 422);
        }

        // Delete existing requirements and create new ones
        CourseOutcome::where('course_id', $request->course_id)->delete();

        foreach($request->outcomes as $outcome){
            CourseOutcome::create([
                'course_id' => $request->course_id,
                'title' => $outcome['title'],
            ]);
        }
        
        $data =  CourseOutcome::where('course_id', $request->course_id)->get();            
        return jsonResponse(true, 'Outcomes created', $data);
    }

    public function savePrice(Request $request)
    {
        $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
        ]);

        $course = Course::find($request->course_id);

        $course->update([
            'price' => $request->price,
            'discount_price' => $request->discount_price,
        ]);

        return jsonResponse(true, 'Course price updated successfully.', $course);
    }

    public function saveSeo(Request $request)
    {
        $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'meta_title' => 'required|string|max:190',
            'meta_description' => 'required|string|max:255',
            'meta_keyword' => 'required|string|max:255',
        ]);

        $find = ['course_id' => $request->course_id];
        $data = CourseSeo::updateOrCreate($find,$request->toArray());
        return jsonResponse(true, 'Course SEO information updated successfully.', $data);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $course = Course::find($id);
        if (!$course) {
            return jsonResponse(false, 'Course not found in our database', null, 404);
        }

        $course->delete();
        return jsonResponse(true, 'Course deleted successfully', null);

    }
}
