<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\Exam;
use App\Models\SchoolAggrement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $examQuestions = ExamQuestion::query();
		
        // Filter by exam_id if provided
        if (!empty($request->exam_id)) {
            $examQuestions = $examQuestions->where('exam_id', $request->exam_id);
        }

        // Search by question text
        if (!empty($request->search)) {
            $examQuestions = $examQuestions->where('question', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if (!empty($request->status)) {
            $examQuestions = $examQuestions->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');

        if (in_array($sortBy, ['id', 'exam_id', 'marks', 'status', 'created_at'])) {
            $examQuestions = $examQuestions->orderBy($sortBy, $sortDirection);
        } else {
            $examQuestions = $examQuestions->orderBy('id', 'DESC');
        }

        // Pagination
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        $paginated = $examQuestions->with(['exam', 'options'])->paginate($perPage, ['*'], 'page', $page);

        return jsonResponse(true, 'Exam questions fetched successfully', [
            'exam_questions' => $paginated->items(),
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
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'question' => 'required|string|max:1000',
            'status' => 'required|in:Active,Block',
            'options' => 'required|array|size:4',
            'options.*.option' => 'required|string|max:500',
            'options.*.is_correct' => 'required|in:yes,no',
        ]);

        try {
            DB::beginTransaction();

            $examQuestion = ExamQuestion::create($request->only(['exam_id', 'question', 'marks', 'status']));

            // Create options
            foreach ($request->options as $option) {
                ExamQuestionOption::create([
                    'question_id' => $examQuestion->id,
                    'option' => $option['option'],
                    'is_correct' => $option['is_correct'],
                ]);
            }

            DB::commit();

            return jsonResponse(true, 'Exam question created successfully', [
                'exam_question' => $examQuestion->load(['exam', 'options'])
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(false, 'Failed to create exam question: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $examQuestion = ExamQuestion::with(['exam', 'options'])->find($id);

        if (!$examQuestion) {
            return jsonResponse(false, 'Exam question not found', null, 404);
        }

        return jsonResponse(true, 'Exam question fetched successfully', [
            'exam_question' => $examQuestion
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $examQuestion = ExamQuestion::find($id);

        if (!$examQuestion) {
            return jsonResponse(false, 'Exam question not found', null, 404);
        }

        $validated = $request->validate([
            'exam_id' => 'sometimes|required|exists:exams,id',
            'question' => 'sometimes|required|string|max:1000',
            'marks' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|required|in:Active,Block',
            'options' => 'sometimes|required|array|size:4',
            'options.*.option' => 'required|string|max:500',
            'options.*.is_correct' => 'required|in:yes,no',
        ]);

        try {
            DB::beginTransaction();

            // Update question
            $examQuestion->update($request->only(['exam_id', 'question', 'marks', 'status']));

            // Update options if provided
            if ($request->has('options')) {
                // Delete existing options
                ExamQuestionOption::where('question_id', $examQuestion->id)->delete();

                // Create new options
                foreach ($request->options as $option) {
                    ExamQuestionOption::create([
                        'question_id' => $examQuestion->id,
                        'option' => $option['option'],
                        'is_correct' => $option['is_correct'],
                    ]);
                }
            }

            DB::commit();

            return jsonResponse(true, 'Exam question updated successfully', [
                'exam_question' => $examQuestion->load(['exam', 'options'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(false, 'Failed to update exam question: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $examQuestion = ExamQuestion::find($id);

        if (!$examQuestion) {
            return jsonResponse(false, 'Exam question not found', null, 404);
        }

        $examQuestion->delete();

        return jsonResponse(true, 'Exam question deleted successfully');
    }

    /**
     * Get all questions for a specific exam.
     */
    public function getByExam(string $examId)
    {
        $exam = Exam::find($examId);

        if (!$exam) {
            return jsonResponse(false, 'Exam not found', null, 404);
        }

        $questions = ExamQuestion::where('exam_id', $examId)
            ->where('status', 'Active')
            ->with('options')
            ->orderBy('id')
            ->get();

        return jsonResponse(true, 'Exam questions fetched successfully', [
            'exam' => $exam,
            'questions' => $questions,
            'total_questions' => $questions->count(),
            'total_marks' => $questions->sum('marks'),
        ]);
    }

    /**
     * Export questions to CSV file
     * CSV format: questionid, question, option1, option2, option3, option4, answers, status
     */
    public function exportQuestions(string $examId)
    {
        $exam = Exam::find($examId);

        if (!$exam) {
            return jsonResponse(false, 'Exam not found', null, 404);
        }

        $questions = ExamQuestion::where('exam_id', $examId)
            ->with('options')
            ->orderBy('id')
            ->get();

        // Create CSV content with proper header
        $csvArray = [
            ['questionid', 'question', 'option1', 'option2', 'option3', 'option4', 'answers', 'status']
        ];

        foreach ($questions as $question) {
            $options = $question->options;
            $optionsArray = [];
            $correctAnswer = 0;

            foreach ($options as $index => $option) {
                $optionsArray[] = $option->option;
                if ($option->is_correct === 'yes') {
                    $correctAnswer = $index + 1;
                }
            }

            // Ensure we have exactly 4 options
            while (count($optionsArray) < 4) {
                $optionsArray[] = '';
            }

            $csvArray[] = [
                $question->id,
                $question->question,
                $optionsArray[0],
                $optionsArray[1],
                $optionsArray[2],
                $optionsArray[3],
                $correctAnswer,
                $question->status === 'Active' ? '1' : '0',
            ];
        }

        // Build CSV string with proper escaping
        $csv = '';
        foreach ($csvArray as $row) {
            $csv .= implode(',', array_map(function($field) {
                // Escape quotes and wrap in quotes if needed
                if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
                    return '"' . str_replace('"', '""', $field) . '"';
                }
                return $field;
            }, $row)) . "\n";
        }

        $filename = 'exam_questions_' . $examId . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response($csv, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
    }

    /**
     * Bulk import questions from CSV file
     * CSV format: questionid, question, option1, option2, option3, option4, answers, status
     * questionid: empty means new question, filled means update existing
     * answers: 1-4 (which option is correct)
     * status: 1 (Active), 0 (Block)
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        $exam = Exam::find($request->exam_id);

		// Check if exam belongs to tutor
		$tutorId = auth('sanctum')->user()->id;
		$schoolInfo = SchoolAggrement::where('user_id', $tutorId)->first();
		if ($schoolInfo == null) {
			return jsonResponse(false, 'School not found', [],404);
		}

        if (!$exam) {
            return jsonResponse(false, 'Exam not found', null, 404);
        }

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $handle = fopen($file->getRealPath(), 'r');
            
            $header = null;
            $created = 0;
            $updated = 0;
            $failed = 0;
            $errors = [];
            $rowNumber = 0;

            if ($handle) {
                while (($row = fgetcsv($handle, 0, ',')) !== false) {
                    $rowNumber++;

                    // Skip header row
                    if ($rowNumber === 1) {
                        $header = $row;
                        continue;
                    }

                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    try {
                        // Map CSV columns to variables
                        $questionId = !empty($row[0]) ? trim($row[0]) : null;
                        $questionText = trim($row[1] ?? '');
                        $option1 = trim($row[2] ?? '');
                        $option2 = trim($row[3] ?? '');
                        $option3 = trim($row[4] ?? '');
                        $option4 = trim($row[5] ?? '');
                        $correctAnswer = trim($row[6] ?? '');
                        $status = trim($row[7] ?? '1');

                        // Validate required fields
                        if (empty($questionText) || empty($correctAnswer)) {
                            $failed++;
                            $errors[] = "Row {$rowNumber}: Question text and correct answer are required";
                            continue;
                        }

                        // Validate all 4 options are provided
                        if (empty($option1) || empty($option2) || empty($option3) || empty($option4)) {
                            $failed++;
                            $errors[] = "Row {$rowNumber}: All 4 options must be provided";
                            continue;
                        }

                        // Validate correct answer is 1-4
                        if (!in_array($correctAnswer, ['1', '2', '3', '4'])) {
                            $failed++;
                            $errors[] = "Row {$rowNumber}: Correct answer must be 1, 2, 3, or 4";
                            continue;
                        }

                        // Convert status (1=Active, 0=Block or any other value)
                        $statusValue = $status == '1' ? 'Active' : 'Block';

                        // Map answer index to options array
                        $options = [
                            ['option' => $option1, 'is_correct' => $correctAnswer == '1' ? 'yes' : 'no'],
                            ['option' => $option2, 'is_correct' => $correctAnswer == '2' ? 'yes' : 'no'],
                            ['option' => $option3, 'is_correct' => $correctAnswer == '3' ? 'yes' : 'no'],
                            ['option' => $option4, 'is_correct' => $correctAnswer == '4' ? 'yes' : 'no'],
                        ];

                        // Check if update or create
                        if (!empty($questionId)) {
                            // Update existing question
                            //$examQuestion = ExamQuestion::find($questionId);
							$examQuestion = ExamQuestion::where('id',$questionId)
											->where('exam_id', $request->exam_id)
											->first();

                            if (!$examQuestion) {
                                $failed++;
                                $errors[] = "Row {$rowNumber}: Question with ID {$questionId} not found";
                                continue;
                            }

                            // Update question data
                            $examQuestion->update([
                                'question' => $questionText,
                                'status' => $statusValue,
                            ]);

                            // Delete existing options and create new ones
                            ExamQuestionOption::where('question_id', $examQuestion->id)->delete();

                            foreach ($options as $option) {
                                ExamQuestionOption::create([
                                    'question_id' => $examQuestion->id,
                                    'option' => $option['option'],
                                    'is_correct' => $option['is_correct'],
                                ]);
                            }

                            $updated++;
                        } else {
                            // Create new question
                            $examQuestion = ExamQuestion::create([
                                'exam_id' => $request->exam_id,
                                'question' => $questionText,
                                'marks' => 1, // Default marks, can be updated later
                                'status' => $statusValue,
                            ]);

                            // Create options
                            foreach ($options as $option) {
                                ExamQuestionOption::create([
                                    'question_id' => $examQuestion->id,
                                    'option' => $option['option'],
                                    'is_correct' => $option['is_correct'],
                                ]);
                            }

                            $created++;
                        }
                    } catch (\Exception $e) {
                        $failed++;
                        $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    }
                }

                fclose($handle);
            }

            DB::commit();

            return jsonResponse(true, 'Bulk import completed', [
                'created' => $created,
                'updated' => $updated,
                'failed' => $failed,
                'errors' => $errors,
                'summary' => [
                    'total_processed' => $created + $updated + $failed,
                    'successful' => $created + $updated,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(false, 'Failed to import questions: ' . $e->getMessage(), null, 500);
        }
    }
}
