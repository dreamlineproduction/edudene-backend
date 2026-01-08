<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SchoolUser;

use Illuminate\Http\Request;

class TutorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::with('tutor')
            ->whereIn('role_id', [2, 4]);

        if (!empty($request->search)) {
            $search = $request->search;

            $users->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('tutor', function ($tutor) use ($search) {
                      $tutor->where('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->get('sort_by', 'full_name');
        $sortDirection = $request->get('sort_direction', 'asc');

        if (in_array($sortBy, ['id', 'full_name', 'email', 'timezone', 'login_provider', 'created_at'])) {
            $users->orderBy($sortBy, $sortDirection);
        } else {
            $users->orderBy('full_name', 'asc');
        }

        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        $paginated = $users->paginate($perPage, ['*'], 'page', $page);

        $users = collect($paginated->items())->map(function ($user) {

            $user->formatted_last_login_datetime = $user->last_login_datetime
                ? formatDisplayDate($user->last_login_datetime, 'd-M-Y H:i:A')
                : null;

            $user->school_tutor = SchoolUser::where('user_id', $user->id)->exists();

            return $user;
        });

        return jsonResponse(true, 'User fetched successfully', [
            'users' => $users,
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
        $user = User::with('tutor')->whereIn('role_id', [2,4])->find($id);

        if (empty($user)) {
            return jsonResponse(false, 'User not found in our database', null, 404);
        }

        $user->formatted_last_login_datetime = $user->last_login_datetime
                ? formatDisplayDate($user->last_login_datetime, 'd-M-Y H:i:A')
                : null;
        return jsonResponse(true, 'User fetched successfully', ['user' => $user]);
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
