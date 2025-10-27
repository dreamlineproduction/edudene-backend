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
        // Implement the logic to change user password
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        $user = auth('sanctum')->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return jsonResponse(false, 'Old password is incorrect.', null, 400);
        }

         // Update new password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);
        return jsonResponse(true, 'Password changed successfully.');
        
    }
}
