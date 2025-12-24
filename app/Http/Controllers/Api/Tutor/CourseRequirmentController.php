<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseRequirement;
use Illuminate\Http\Request;

class CourseRequirmentController extends Controller
{
	 /**
     * Save course requirements
     */
    public function saveRequirement(Request $request)
    {
        $validation =[     
            'course_id' => 'required|integer|exists:courses,id',     
            'title' => 'required|string|max:255',
        ];

        $request->validate($validation);
        
		$requirement = CourseRequirement::create([
			'course_id' => $request->course_id,
			'title' => $request->title,
			'sort' => 1000,
		]);

        return jsonResponse(true, 'Requirement added successfully.', ['requirement' => $requirement	]);
    }

	 /**
     * Fetch requirements of a course
     */
    public function getRequirements(string $courseId)
    {
        try {
            $course = Course::find($courseId);
            
            if (!$course) {
                return jsonResponse(false, 'Course not found in our database', null, 404);
            }

            $requirements = CourseRequirement::where('course_id', $courseId)
							->orderBy('sort','asc')
							->get();

            if ($requirements->isEmpty()) {
                return jsonResponse(true, 'No requirements found for this course', []);
            }

            return jsonResponse(true, 'Course requirements retrieved successfully', ['requirements' => $requirements]);
        } catch(\Exception $e) {
            return jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

	/**
	 * Update sort order of course requirements
	 */
	public function updateSortOrder(Request $request)
	{
		try {
			$request->validate([
				'course_id' => 'required|integer|exists:courses,id',
				'requirements' => 'required|array',
				'requirements.*.id' => 'required|integer|exists:course_requirements,id',
				'requirements.*.sort' => 'required|integer|min:1',
			]);

			$course = Course::find($request->course_id);
			if (!$course) {
				return jsonResponse(false, 'Course not found in our database', null, 404);
			}

			foreach ($request->requirements as $item) {
				CourseRequirement::where('id', $item['id'])
					->where('course_id', $request->course_id)
					->update(['sort' => $item['sort']]);
			}

			$requirements = CourseRequirement::where('course_id', $request->course_id)
				->orderBy('sort', 'asc')
				->get();

			return jsonResponse(true, 'Sort order updated successfully', ['requirements' => $requirements]);
		} catch(\Exception $e) {
			return jsonResponse(false, $e->getMessage(), null, 500);
		}
	}

	/**
	 * Update a specific requirement
	 */
	public function updateRequirement(Request $request, $id)
	{
		try {
			$requirement = CourseRequirement::find($id);
			if (!$requirement) {
				return jsonResponse(false, 'Requirement not found', null, 404);
			}

			$validation = [
				'title' => 'required|string|max:255',
			];

			$request->validate($validation);

			$requirement->update([
				'title' => $request->title,
			]);

			return jsonResponse(true, 'Requirement updated successfully', ['requirement' => $requirement]);
		} catch(\Exception $e) {
			return jsonResponse(false, $e->getMessage(), null, 500);
		}
	}

	/**
	 * Delete a specific requirement
	 */
	public function deleteRequirement($id)
	{
		try {
			$requirement = CourseRequirement::find($id);
			if (!$requirement) {
				return jsonResponse(false, 'Requirement not found', null, 404);
			}

			$requirement->delete();

			return jsonResponse(true, 'Requirement deleted successfully', null);
		} catch(\Exception $e) {
			return jsonResponse(false, $e->getMessage(), null, 500);
		}
	}
}