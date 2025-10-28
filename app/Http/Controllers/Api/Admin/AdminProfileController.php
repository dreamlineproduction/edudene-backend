<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminProfileController extends Controller
{
    //
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required',
        ]);
        
        $user = auth('sanctum')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return jsonResponse(false, "We couldn't verify your current password. If you forgot it, use “Forgot password”", null, 400);
        }

        if($request->current_password === $request->new_password){
            return jsonResponse(false, "The new password cannot be the same as the current password.", null, 400);
        }
        

        // Update new password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
        return jsonResponse(true, 'Password changed successfully.', null, 200);        
    }
}
