<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AccountActivationController extends Controller
{
    //

    public function activateUserAccount(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json([
                'status' => 400,
                'message' => 'Activation token is required',
            ], 400);
        }

        $user =User::where('remember_token', $token)->first();

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Invalid activation token',
            ], 404);
        }

        if ($user->status === 'Active') {
            return response()->json([
                'status' => 200,
                'message' => 'Account is already activated',
            ], 200);
        }

        $user->status = 'Active';
        $user->remember_token = null;
        $user->email_verified_at = now();        
        $user->save();

        return response()->json([
            'status' => 200,
            'message' => 'Account activated successfully',
        ], 200);
    }
}
