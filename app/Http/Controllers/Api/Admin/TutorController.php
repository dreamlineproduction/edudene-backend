<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TutorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $users = User::query()->where('role_id', 2);

		if (!empty($request->search)) {
			$users = $users->where('title','like','%'.$request->search.'%');
		}

		$sortBy = $request->get('sort_by', 'full_name');
    	$sortDirection = $request->get('sort_direction', 'asc');

		if (in_array($sortBy, ['id', 'full_name', 'email', 'phone_number','timezone', 'login_provider', 'created_at'])) {
			$users = $users->orderBy($sortBy, $sortDirection);
		} else {
			$users = $users->orderBy('full_name', 'asc');
		}

		$perPage = (int) $request->get('per_page', 10);
    	$page = (int) $request->get('page', 1);

		$paginated = $users->paginate($perPage, ['*'], 'page', $page);


		return jsonResponse(true, 'User fetched successfully', [
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
        //
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
        //
    }
}
