<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Classes;
use App\Models\SchoolAggrement;
use App\Models\SchoolUser;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request) {
		$exams = Exam::query();


		$sortBy = $request->get('sort_by');
    	$sortDirection = $request->get('sort_direction', 'desc');

		if (in_array($sortBy, ['id', 'status', 'created_at'])) {
			$exams = $exams->orderBy($sortBy, $sortDirection);
		} else {
			$exams = $exams->orderBy('id', 'DESC');
		}

		if (!empty($request->search)) {
			$search = $request->search;
		     $exams = $exams->where(function ($q) use ($search) {
	        	$q->where('id', 'LIKE', "%{$search}%")
	          	->orWhereHas('class.category_level_four', function ($q2) use ($search) {
		              $q2->where('title', 'LIKE', "%{$search}%");
		          })
	        	->orWhereHas('class.tutor', function ($q3) use ($search) {
		              $q3->where('full_name', 'LIKE', "%{$search}%");
		          })
            	->orWhereHas('class.school', function ($q4) use ($search) {
              		$q4->where('school_name', 'LIKE', "%{$search}%");
          		});
		    });
		}	

		$perPage = (int) $request->get('per_page', 10);
    	$page = (int) $request->get('page', 1);

		$paginated = $exams->with(	'class:id,category_level_four_id,tutor_id,school_id',
									'class.category_level_four:id,title',
									'class.school:id,school_name',
									'class.tutor:id,full_name',
								)
		->paginate($perPage, ['*'], 'page', $page);

		return jsonResponse(true, 'Exams fetched successfully', [
			'exams' => $paginated->items(),
			'total' => $paginated->total(),
			'current_page' => $paginated->currentPage(),
			'per_page' => $paginated->perPage(),
		]);
	}

	public function store(Request $request) {
		$tutorId = auth('sanctum')->user()->id;
		$schoolInfo = SchoolUser::where('user_id',$tutorId)->first();

		if ($schoolInfo == null) {
			return jsonResponse(false, 'School not found', []);
		}

		$request->validate([
            'class_id' => 'required|exists:classes,id',
            'enable'   => 'required|in:0,1',
            'no_of_questions' => 'required|integer|min:1',
            'total_exam_marks'  => 'required|numeric|min:0',
            'min_pass_marks'  => 'required|numeric|min:1',
            'duration'  => 'required|numeric',
            'retake_fee'  => 'required|numeric|min:0',
            'expiry_date'  => 'required|date|date_format:Y-m-d',
        ]);

		$exam = Exam::updateOrCreate(
			['class_id' => $request->class_id],
			[
				'enable' => $request->enable,
				'no_of_questions' => $request->no_of_questions,
				'total_exam_marks' => $request->total_exam_marks,
				'min_pass_marks' => $request->min_pass_marks,
				'duration' => $request->duration,
				'retake_fee' => $request->retake_fee,
				'expiry_date' => $request->expiry_date,
				'school_id' => $schoolInfo->school_id
			]
		);

		return jsonResponse(true, 'Exam saved successfully', ['exam' => $exam]);
	}

	// public function show($exam_id) {
		
	// 	$tutorId = auth('sanctum')->user()->id;
	// 	$roleId = auth('sanctum')->user()->role_id;
		
	// 	// Check if this exam belongs to a school
	// 	// Check if this class belongs to a tutor

	// 	// Checked if users belongs to a school
		

	// 	if ($roleId === 2 || $roleId === 4) { // It means it is a school tutor or tutor
	// 		$schoolInfo = SchoolAggrement::where('user_id', $tutorId)->first();
			
	// 		if ($schoolInfo == null) {
	// 			return jsonResponse(false, 'School not found', [], 404);
	// 		}

	// 		$exam = Exam::select('exams.*')
	// 				->where('exams.id',$exam_id)
	// 				->with('class','class.category_level_four:id,title','class.tutor:id,full_name','class.school:id,school_name')
	// 				->leftJoin('classes', 'classes.id', '=', 'exams.class_id')
	// 				->where('classes.school_id', $schoolInfo->school_id)
	// 				->where('classes.tutor_id', $tutorId)
	// 				->first();
			
	// 	} else {
	// 		$schoolInfo = SchoolUser::where('user_id', $tutorId)->first();
			
	// 		if ($schoolInfo == null) {
	// 			return jsonResponse(false, 'School not found', [], 404);
	// 		}

	// 		// It means it is a school and  this class belongs to this school
	// 		$exam = Exam::select('exams.*')
	// 				->where('exams.id',$exam_id)
	// 				->with('class','class.category_level_four:id,title','class.tutor:id,full_name','class.school:id,school_name')
	// 				->leftJoin('classes', 'classes.id', '=', 'exams.class_id')
	// 				->where('classes.school_id', $schoolInfo->school_id)
	// 				->first();
	// 	}
		
	// 	if ($exam == null) {
	// 		return jsonResponse(false, 'Exam not found', [],404);
	// 	}

	// 	return jsonResponse(true, 'Exam found', ['exam' => $exam]);
	// }

	public function show($exam_id) {

		// Get authenticated user details
		$tutorId = auth('sanctum')->user()->id;
		$roleId = auth('sanctum')->user()->role_id;

		// Determine access based on user role
		// Roles 2 and 4 represent tutors (school tutor or individual tutor)
		if ($roleId === 2 || $roleId === 4) {

			// Fetch school association for the tutor
			$schoolInfo = SchoolAggrement::where('user_id', $tutorId)->first();

			// Return error if tutor is not associated with any school
			if ($schoolInfo == null) {
				return jsonResponse(false, 'School not found', [], 404);
			}

			// Retrieve exam ensuring:
			// - Exam belongs to the tutor's school
			// - Exam is assigned to the authenticated tutor
			$exam = Exam::select('exams.*')
				->where('exams.id', $exam_id)
				->with(
					'class',
					'class.category_level_four:id,title',
					'class.tutor:id,full_name',
					'class.school:id,school_name'
				)
				->leftJoin('classes', 'classes.id', '=', 'exams.class_id')
				->where('classes.school_id', $schoolInfo->school_id)
				->where('classes.tutor_id', $tutorId)
				->first();

		} else {

			// Fetch school association for non-tutor users (e.g., school admin/staff)
			$schoolInfo = SchoolUser::where('user_id', $tutorId)->first();

			// Return error if user is not associated with any school
			if ($schoolInfo == null) {
				return jsonResponse(false, 'School not found', [], 404);
			}

			// Retrieve exam ensuring it belongs to the user's school
			$exam = Exam::select('exams.*')
				->where('exams.id', $exam_id)
				->with(
					'class',
					'class.category_level_four:id,title',
					'class.tutor:id,full_name',
					'class.school:id,school_name'
				)
				->leftJoin('classes', 'classes.id', '=', 'exams.class_id')
				->where('classes.school_id', $schoolInfo->school_id)
				->first();
		}

		// Return error if exam is not found or does not meet access conditions
		if ($exam == null) {
			return jsonResponse(false, 'Exam not found', [], 404);
		}

		// Return successful response with exam data
		return jsonResponse(true, 'Exam found', ['exam' => $exam]);
	}

	public function getClasses(Request $request) {
		$classes = Classes::orderBy('id','desc')
					->whereDoesntHave('exam');

		if (!empty($request->search)) {
			$search = $request->search;

	        $classes = $classes->whereHas('category_level_four', function ($query) use ($search) {
	            $query->where('title', 'LIKE', "%{$search}%");
	        });
		}		

		$classes = $classes
			->with('category_level_four')
			->with('tutor:id,full_name')
			->limit(50)
			->get();

		return jsonResponse(true, 'Classes found', ['classes' => $classes]);
	}
}
