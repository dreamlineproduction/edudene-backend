<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Classes;
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

	public function show($exam_id) {
		$exam = Exam::find($exam_id);
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
