<?php

namespace App\Http\Controllers\APi\Admin;

use App\Http\Controllers\Controller;
use App\Mail\User\EmailChangeRequestStatus;
use App\Models\EmailChangeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailChangeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $sortBy = $request->get('sort_by', 'title');
    	$sortDirection = $request->get('sort_direction', 'asc');
        $perPage = (int) $request->get('per_page', 10);
    	$page = (int) $request->get('page', 1);

        $query = EmailChangeRequest::query();
        $query->select(
            'email_change_requests.id',
            'email_change_requests.user_id',
            'email_change_requests.email',
            'email_change_requests.new_email',
            'email_change_requests.reason',
            'email_change_requests.decline_text',
            'email_change_requests.status',
            'users.full_name',
        );

        if($request->get('search'))
        {
            $query = $query->where('users.full_name','like','%'.$request->search.'%');
            $query = $query->orWhere('email_change_requests.email','like','%'.$request->search.'%');
            $query = $query->orWhere('email_change_requests.new_email','like','%'.$request->search.'%');
            $query = $query->orWhere('email_change_requests.status','like','%'.$request->search.'%');
        }

        if (in_array($sortBy, ['id', 'full_name','new_email', 'email', 'status', 'created_at'])) {
			$query = $query->orderBy($sortBy, $sortDirection);
		} else {
			$query = $query->orderByRaw("
                FIELD(email_change_requests.status, 'Pending', 'Approved', 'Declined')
            ")->orderBy('email_change_requests.created_at', 'desc');
		}

        $query->leftJoin('users', 'users.id', '=', 'email_change_requests.user_id');
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        return jsonResponse(true, 'Email change requests fetched successfully', [
            'users' => $paginated->items(),
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validation = [
            'status' => 'required|in:Approved,Declined',
        ];

        if($request->status === 'Declined'){
            $validation['reason'] = 'required|string|max:255';
        }

        $request->validate($validation);


        $data = EmailChangeRequest::find($id);
        if(empty($data)) {
            return jsonResponse(false, 'Data not found', [], 404);
        }

        $data->update([
            'status' => $request->status,
            'decline_text' => $request->reason,
        ]);

        $user = User::where('id', $data->user_id)->first();
        $oldEmail = $user->email;
        
        if($request->status === 'Approved'){
            $user->update([
                'email' => $data->new_email
            ]);

            // Delete the email change request
            $data->delete();           
        }


        if($request->status === 'Declined'){
            // Delete the email change request       
        }

        $user = User::where('id', $data->user_id)->first();

        $mailData = [
            'fullName' => $user->full_name,
            'status' => $request->status,
            'reason' => $request->reason,
        ];

        try{
           Mail::to($oldEmail)->send(new EmailChangeRequestStatus($mailData)); 
        } catch (\Exception $e) {
            return jsonResponse(false, 'Something went wrong', [], 500);
        }

        return jsonResponse(true, 'Email change request updated successfully', ['user' => $data]);
    } 
}
