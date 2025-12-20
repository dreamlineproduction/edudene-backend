<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\ClassSessions;
use DateInterval,DatePeriod,DateTime;
use Illuminate\Http\Request;

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

        $query = Classes::query()
            ->select(
                'classes.*',
                'tutors.full_name as tutor_name',
                'schools.full_name as school_author',
                'schools.email as school_email',
                'category_level_fours.title as subject'
            )
            ->leftJoin('users as tutors', 'tutors.id', '=', 'classes.tutor_id')
            ->leftJoin('users as schools', 'schools.id', '=', 'classes.school_id')
            ->leftJoin(
                'category_level_fours',
                'category_level_fours.id',
                '=',
                'classes.category_level_four_id'
            );

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('tutors.full_name', 'like', "%{$search}%")
                ->orWhere('schools.email', 'like', "%{$search}%")
                ->orWhere('category_level_fours.title', 'like', "%{$search}%");
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

        return jsonResponse(true, 'Classes fetched successfully', [
            'users' => $paginated->items(),
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
        // Delete class
        $data = Classes::find($id);
        if (empty($data)) {
            return jsonResponse(false, 'Class not found in our database', null, 404);
        }

        $data->delete();
        return jsonResponse(true, 'Class deleted successfully');
    }
}
