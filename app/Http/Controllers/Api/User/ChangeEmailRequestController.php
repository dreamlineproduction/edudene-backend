<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\EmailChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangeEmailRequestController extends Controller
{
    
    public function index()
    {
        $user = auth('sanctum')->user();
        $lastRequest = EmailChangeRequest::where(['user_id'=> $user->id])->first();

        return jsonResponse(true, 'Request list', [
            'last_request' => $lastRequest
        ]);

    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'new_email' => 'required|email|unique:users,email',
            'password' => 'required',
            'reason' => 'required',
        ]);

        $user = auth('sanctum')->user();

        if (!(Hash::check($request->password, $user->password))) {
            return jsonResponse(false, 'Your current password does not matches with the password you provided. Please try again.', null, 400);
        } 

        if ($request->email === $request->new_email) {
            return jsonResponse(false, 'Your current email and new email can not be same. Please try again.', null, 400);
        }
        
        $count = EmailChangeRequest::where(['user_id' => $user->id, 'status' => 'Pending'])->count();
        if ($count > 0) {
            return jsonResponse(false, 'Error your request already pending.', null, 203);                
        }

        $request['user_id'] = $user->id;
        $lastRequest = EmailChangeRequest::create($request->toArray());
        return jsonResponse(true,'',[
            'last_request' => $lastRequest
        ]);
            
    }
}
