<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $schools = School::with('user:id,full_name,email');

        if (!empty($request->search)) {
            $search = $request->search;

            $schools->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('tutor', function ($tutor) use ($search) {
                      $tutor->where('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->get('sort_by', 'school_name');
        $sortDirection = $request->get('sort_direction', 'asc');

        if (in_array($sortBy, ['id', 'full_name', 'email', 'timezone', 'login_provider', 'created_at'])) {
            $schools->orderBy($sortBy, $sortDirection);
        } else {
            $schools->orderBy('school_name', 'asc');
        }

        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        $paginated = $schools->paginate($perPage, ['*'], 'page', $page);

        $schools = collect($paginated->items())->map(function ($user) {

            $user->formatted_last_login_datetime = $user->last_login_datetime
                ? formatDisplayDate($user->last_login_datetime, 'd-M-Y H:i:A')
                : null;


            return $user;
        });

        return jsonResponse(true, 'School fetched successfully', [
            'schools' => $schools,
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
        $school = School::with('user:id,full_name,email')->find($id);

        if (empty($school)) {
            return jsonResponse(false, 'School not found in our database', null, 404);
        }


        return jsonResponse(true, 'School fetched successfully', ['school' => $school]);
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
    }
}
