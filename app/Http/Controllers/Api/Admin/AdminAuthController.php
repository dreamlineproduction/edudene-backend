<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    //
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'timezone' => 'nullable|string|max:150',
            'login_datetime' => 'nullable|string|max:150',
        ]);

        $user = User::where(['email'=>$request->email,'role_id'=>5])->first();

        // Check if user exists
        if (!$user) {
            return jsonResponse(true, 'User not found in our database.', [],404);                
        }

        // Check if user is temporarily inactive due to too many failed login attempts
        if ($user->temporary_status === 'Inactive') {
            return jsonResponse(true, 
                'Your account is temporarily inactive due to too many failed login attempts.Please try again later or contact support.', 
            [],423);             
        }

        // Check if user is inactive
        if ($user->status === 'Inactive') {
            return jsonResponse(true, 'Your account is inactive. Please activate your account.',[],423);            
        }

        // Check password
        if (!Hash::check($request->password, $user->password)) {
            $loginAttempt =  LoginAttempt::where('email', $request->email)->first();
            
                // If attempts exceed 5, user account temporary status Inactive.
                if(notEmpty($loginAttempt) && $loginAttempt->attempt_count >= 5){

                    $user->temporary_status = 'Inactive';
                    $user->save();

                    return jsonResponse(true, 'Too many login attempts. Please try again later.',[],429);                     
                } 
                
                // Log the failed login attempt
                $find = ['email' => $request->email];
                LoginAttempt::updateOrCreate($find,[
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'email' => $request->email,   
                    'attempt_count' => notEmpty($loginAttempt) ? $loginAttempt->attempt_count  + 1 : 1,   
                    'locked_datetime' => $request->login_datetime ?? now(),      
                    'timezone' => getDefaultTimezone($request->timezone),                
                ]);

            return jsonResponse(true, 'Sorry, your password was incorrect. Please double-check your password.',[],401);                               
        }

        // Clear login attempts on successful login
        LoginAttempt::where('email', $request->email)->delete();



        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        $data['user'] = $user;
        $data['token'] = $token;
        return jsonResponse(true, 'Login successful.',$data);                    
    }
}
