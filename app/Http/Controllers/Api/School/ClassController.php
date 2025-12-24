<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Mail\Tutor\ClassStatus;
use App\Mail\User\EmailChangeRequestStatus;
use App\Models\Classes;
use App\Models\ClassSessions;
use App\Models\School;
use DateInterval,DatePeriod,DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        $loggedInUser = auth('sanctum')->user();

        $query = Classes::query()
            ->select(
                'classes.*',
                'tutors.full_name as tutor_name',
                'tutors.email as tutor_email',
                'schools.full_name as school_author',
                'schools.email as school_email',
                'category_level_fours.title as subject',
                'class_types.title as class_type'
            )
            ->where('school_id',$loggedInUser->id)
            ->leftJoin('users as tutors', 'tutors.id', '=', 'classes.tutor_id')
            ->leftJoin('users as schools', 'schools.id', '=', 'classes.school_id')
            ->leftJoin('category_level_fours','category_level_fours.id','=','classes.category_level_four_id')
            ->leftJoin('class_types','class_types.id','=','classes.class_type_id');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('tutors.full_name', 'like', "%{$search}%")
                ->orWhere('tutors.email', 'like', "%{$search}%")
                ->orWhere('schools.email', 'like', "%{$search}%")
                ->orWhere('class_types.title', 'like', "%{$search}%")
                ->orWhere('classes.status', 'like', "%{$search}%");
            });
        }

        if (in_array($sortBy, [
            'id',
            'tutor_name',
            'school_author',
            'school_email',
            'subject',
            'created_at'
        ])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderByRaw("
                FIELD(classes.status, 'Pending', 'Approved', 'Declined')
            ")->orderBy('classes.created_at', 'desc');
        }

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $classes = collect($paginated->items())->map(function ($class) {

            $class->formatted_start_date = formatDisplayDate($class->start_date);
            $class->formatted_end_date   = formatDisplayDate($class->end_date);
            $class->timeline   = minutesToHours($class->duration);

            $duration = calculateDuration($class->start_date,$class->end_date);
            if (!$duration) {
                $class->duration = null;
            }

            $parts = [];
            if ($duration['years'] > 0) {
                $parts[] = $duration['years'] . ' ' . ($duration['years'] > 1 ? 'Years' : 'Year');
            }

            if ($duration['months'] > 0) {
                $parts[] = $duration['months'] . ' ' . ($duration['months'] > 1 ? 'Months' : 'Month');
            }

            if ($duration['total_days'] > 0) {
                $parts[] = $duration['total_days'] . ' ' . ($duration['days'] > 1 ? 'Days' : 'Day');
            }

            $class->formatted_duration = implode(', ', $parts);
            return $class;
        });

        return jsonResponse(true, 'Classes fetched successfully', [
            'classes' =>$classes,
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
        $validation = [
            'class_type_id' => 'required|exists:class_types,id',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'sub_sub_category_id' => 'required|exists:sub_sub_categories,id',
            'category_level_four_id' => 'required|exists:category_level_fours,id',
            'tutor_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'hour' => 'required|numeric',
            'minute' => 'required|numeric',
            'price' => 'required|numeric',
        ];

        // If class type = 2 then end_date required
        if ((int) $request->class_type_id === 2) {
            $validation['end_date'] = 'required|date';
        }

        $validated = $request->validate($validation);

        $startDate = (new DateTime($request->start_date))->format('Y-m-d');
        $validated['start_date'] = $startDate;


        if(notEmpty($request->end_date)){
            $endDate = (new DateTime($request->end_date))->format('Y-m-d');    
            $validated['end_date'] = $endDate;
        }  else {
            $validated['end_date'] = $startDate;
        }       


        $loggedInUser = auth('sanctum')->user();

        // Create class duration
        $validated['duration'] = ($validated['hour']*60) + $validated['minute'];

        // Merge school_id safely
        $validated['school_id'] = $loggedInUser->id;

        // Create class
        $class = Classes::create($validated);


        $startDate = new DateTime($validated['start_date']);

        $endDate   = new DateTime($validated['end_date']);
        $endDate->modify('+1 day');

        $period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);
        foreach ($period as $date) {
           ClassSessions::create([
                'class_id' => $class->id,
                'school_id' => $loggedInUser->id,
                'tutor_id' => $validated['tutor_id'],
                'start_date' => $date->format('Y-m-d'),
           ]);
        }
        return jsonResponse(true, 'Class created successfully', $class);
    }

    public function show($id)
    {
        $loggedInUser = auth('sanctum')->user();

        $class = Classes::query()
            ->select(
                'classes.*',
                'tutors.full_name as tutor_name',
                'tutors.email as tutor_email',
                'schools.full_name as school_author',
                'schools.email as school_email',
                'category_level_fours.title as subject',
                'class_types.title as class_type'
            )
            ->where('classes.school_id', $loggedInUser->id)
            ->where('classes.id', $id)
            ->leftJoin('users as tutors', 'tutors.id', '=', 'classes.tutor_id')
            ->leftJoin('users as schools', 'schools.id', '=', 'classes.school_id')
            ->leftJoin('category_level_fours', 'category_level_fours.id', '=', 'classes.category_level_four_id')
            ->leftJoin('class_types', 'class_types.id', '=', 'classes.class_type_id')
            ->first();

        if (!$class) {
            return jsonResponse(false, 'Class not found', 404);
        }

        $class->timeline = minutesToHours($class->duration);

        $duration = calculateDuration($class->start_date, $class->end_date);

        if ($duration) {
            $parts = [];

            if ($duration['years'] > 0) {
                $parts[] = $duration['years'] . ' ' . ($duration['years'] > 1 ? 'Years' : 'Year');
            }

            if ($duration['months'] > 0) {
                $parts[] = $duration['months'] . ' ' . ($duration['months'] > 1 ? 'Months' : 'Month');
            }

            if ($duration['total_days'] > 0) {
                $parts[] = $duration['total_days'] . ' ' . ($duration['total_days'] > 1 ? 'Days' : 'Day');
            }

            $class->formatted_duration = implode(', ', $parts);
        } else {
            $class->formatted_duration = null;
        }

        $class->formatted_start_date = formatDisplayDate($class->start_date);
        $class->formatted_end_date   = formatDisplayDate($class->end_date);

        return jsonResponse(true, 'Class fetched successfully', [
            'classes' => $class
        ]);
    }

     public function update(Request $request, $id)
    {
        $validation = [
            'status' => 'required|in:Approved,Declined',
        ];

        if($request->status === 'Declined'){
            $validation['reason'] = 'required|string|max:255';
            $validation['decline_text'] = $request->reason;
        }


        $request->validate($validation);


        $classes = Classes::with(['tutor','school'])->find($id);
        if(empty($classes)) {
            return jsonResponse(false, 'Data not found', [], 404);
        }   

        
        $classes->update([
            'status'=>$request->status,
            'decline_text'=>$request->reason,
        ]);

        $schoolInfo = School::where('user_id',$classes->school_id)->first();

        $mailData = [
            'fullName' => $classes->tutor->full_name,
            'schoolName' => $schoolInfo->school_name,
            'status' => $request->status,
            'reason' => $request->reason,
        ];

        try{
           Mail::to($classes->tutor->email)->send(new ClassStatus($mailData)); 
        } catch (\Exception $e) {
            return jsonResponse(false, 'Something went wrong', [], 500);
        }

        return jsonResponse(true, 'Status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {        
        // Delete class
        $data = Classes::find($id);
        if (empty($data)) {
            return jsonResponse(false, 'Class not found in our database', null, 404);
        }

        //$data->delete();
        return jsonResponse(true, 'Class deleted successfully');
    }
}
