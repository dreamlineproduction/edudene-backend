<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseOutcome;


class CourseOutcomeController extends Controller
{
	/**
	 * Save course outcome
	 */
	public function saveOutcome(Request $request)
	{
		$validation = [
			'course_id' => 'required|integer|exists:courses,id',
			'title' => 'required|string|max:255',
		];

		$request->validate($validation);

		$outcome = CourseOutcome::create([
			'course_id' => $request->course_id,
			'title' => $request->title,
			'sort' => 1000,
		]);

		return jsonResponse(true, 'Outcome added successfully.', ['outcome' => $outcome]);
	}

	/**
	 * Fetch outcomes of a course
	 */
	public function getOutcomes(string $courseId)
	{
		try {
			$course = Course::find($courseId);

			if (!$course) {
				return jsonResponse(false, 'Course not found in our database', null, 404);
			}

			$outcomes = CourseOutcome::where('course_id', $courseId)
				->orderBy('sort', 'asc')
				->get();

			if ($outcomes->isEmpty()) {
				return jsonResponse(true, 'No outcomes found for this course', []);
			}

			return jsonResponse(true, 'Course outcomes retrieved successfully', ['outcomes' => $outcomes]);
		} catch(\Exception $e) {
			return jsonResponse(false, $e->getMessage(), null, 500);
		}
	}

	/**
	 * Update sort order of course outcomes
	 */
	public function updateSortOrder(Request $request)
	{
		try {
			$request->validate([
				'course_id' => 'required|integer|exists:courses,id',
				'outcomes' => 'required|array',
				'outcomes.*.id' => 'required|integer|exists:course_outcomes,id',
				'outcomes.*.sort' => 'required|integer|min:1',
			]);

			$course = Course::find($request->course_id);
			if (!$course) {
				return jsonResponse(false, 'Course not found in our database', null, 404);
			}

			foreach ($request->outcomes as $item) {
				CourseOutcome::where('id', $item['id'])
					->where('course_id', $request->course_id)
					->update(['sort' => $item['sort']]);
			}

			$outcomes = CourseOutcome::where('course_id', $request->course_id)
				->orderBy('sort', 'asc')
				->get();

			return jsonResponse(true, 'Sort order updated successfully', ['outcomes' => $outcomes]);
		} catch(\Exception $e) {
			return jsonResponse(false, $e->getMessage(), null, 500);
		}
	}

	/**
	 * Update a specific outcome
	 */
	public function updateOutcome(Request $request, $id)
	{
		try {
			$outcome = CourseOutcome::find($id);
			if (!$outcome) {
				return jsonResponse(false, 'Outcome not found', null, 404);
			}

			$validation = [
				'title' => 'required|string|max:255',
			];

			$request->validate($validation);

			$outcome->update([
				'title' => $request->title,
			]);

			return jsonResponse(true, 'Outcome updated successfully', ['outcome' => $outcome]);
		} catch(\Exception $e) {
			return jsonResponse(false, $e->getMessage(), null, 500);
		}
	}

	/**
	 * Delete a specific outcome
	 */
	public function deleteOutcome($id)
	{
		try {
			$outcome = CourseOutcome::find($id);
			if (!$outcome) {
				return jsonResponse(false, 'Outcome not found', null, 404);
			}

			$outcome->delete();

			return jsonResponse(true, 'Outcome deleted successfully', null);
		} catch(\Exception $e) {
			return jsonResponse(false, $e->getMessage(), null, 500);
		}
	}
}

?>