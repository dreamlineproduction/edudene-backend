<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SendWarningToAllUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $users = User::query()->where('role_id', 1);

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
        // Delete User
        $data = User::find($id);
        if (empty($data)) {
            return jsonResponse(false, 'User not found in our database', null, 404);
        }

        $data->delete();
        return jsonResponse(true, 'User deleted successfully');
    }

    public function changeStatus(Request $request, $id){
        $request->validate([
            'status' => 'required|in:Active,Inactive',          
        ]);

        $user = User::find($id);

        if (empty($user)) {
            return jsonResponse(false, 'User not found in our database', null, 404);
        }

        $user->status = $request->status;
        $user->save();
        return jsonResponse(true, 'Status changed successfully');
    }

    public function sendWarning(Request $request, $id){
        
        $request->validate([
            'description' => 'required|string',          
        ]);

        $user = User::find($id);

        if (empty($user)) {
            return jsonResponse(false, 'User not found in our database', null, 404);
        }

        $mailData = [
            'fullName' => $user->full_name,
            'description' => $request->description
        ];

        // Send password reset email
        Mail::to($user->email)->send(
            new SendWarningToAllUser($mailData)
        );

        return jsonResponse(true, 'Role changed successfully');
    }
}
