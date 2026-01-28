<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Tutor;
use App\Models\Classes;
use Illuminate\Http\Request;

class TutorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($roleId = 2)
    {
        $query = User::query()
            ->select('users.id','users.role_id','users.full_name','school_aggrements.is_freelancer')
            ->leftJoin('school_aggrements','school_aggrements.user_id','=','users.id')
            ->where(function($q){
                $q->where('users.role_id',2)
                ->orWhere(function($q2)  {
                    $q2->where('users.role_id',4)
                    ->where('school_aggrements.is_freelancer', 'Yes');
                });
            })
            ->orderBy('full_name','asc');

        $tutors =  $query->get();

        return jsonResponse(true,'Tutor data',['tutors' => $tutors]);
    }


    /**
     * Display a listing of the resource.
     */
    public function popularTeacher(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);


        $query = User::query();
        $query->whereIn('role_id',[2,4])
            ->with('tutor:id,user_id,avatar,avatar_url')
            ->withCount(['course'])
            ->orderBy('full_name','asc');

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $tutors = collect($paginated->items())->map(function ($teacher) {
            $teacher->formatted_created_at = $teacher->created_at ? formatDisplayDate($teacher->created_at, 'd-M-Y H:i:A'): null;
            
            $teacher->total_reviews = 980;
            $teacher->avg_rating = 4.8;
            $teacher->hourly_rate = rand(50,250);

            $teacher->total_courses = Course::where('user_id', $teacher->id)->count();
            $teacher->total_classes = Classes::where('tutor_id', $teacher->id)->count();

            return $teacher;
        });

        return jsonResponse(true, 'User fetched successfully', [
            'tutors' => $tutors,
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }

    
}
