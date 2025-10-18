<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Mail\UserAccountActivationMail;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:150',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5',
            'timezone' => 'required|string|max:150',
        ]);

        

        // Create activation token
        $activationToken = Str::random(90);

        $request->merge([
            'role_id' => 1,
            'user_name' => generateUniqueSlug($request->full_name,'App\Models\User','user_name'),
            'remember_token'=>$activationToken,
            'password' => Hash::make($request->password),
            'timezone' => getDefaultTimezone($request->timezone),
        ]);

        $user = User::create($request->toArray());        
        $activationLink = url('/user/activate-account?token=' . $activationToken);

        $mailData = [
            'fullName' => $user->full_name,
            'activationLink' => $activationLink,
        ];
        
        // Send activation email
        Mail::to($user->email)->send(new UserAccountActivationMail($mailData));

        return response()->json([
            'status' => 200,
            'user' => $user,          
            'message' => 'User registered successfully',         
        ]);
    }



    // 🔑 Login API
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'timezone' => 'nullable|string|max:150',
            'login_datetime' => 'nullable|string|max:150',
        ]);

        $user = User::where(['email'=> $request->email,'role_id'=>1])->first();

        // Check if user exists
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found in our database.',
            ]);
        }

        // Check if user is temporarily inactive due to too many failed login attempts
        if ($user->temporary_status === 'Inactive') {
            return response()->json([
                'status' => 423,
                'message' => 'Your account is temporarily inactive due to too many failed login attempts.Please try again later or contact support.',
            ], 423);
        }

        // Check if user is inactive
        if ($user->status === 'Inactive') {
            return response()->json([
                'status' => 403,
                'message' => 'Your account is inactive. Please activate your account.',
            ], 403);
        }

        // Check password
        if (!Hash::check($request->password, $user->password)) {

            $loginAttempt =  LoginAttempt::where('email', $request->email)->first();
            
            // If attempts exceed 5, user account temporary status Inactive.
            if(!empty($loginAttempt) && $loginAttempt->attempt_count >= 5){

                $user->temporary_status = 'Inactive';
                $user->save();


                return response()->json([
                    'status' => 429,
                    'message' => 'Too many login attempts. Please try again later.',
                ], 429);
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

            return response()->json([
                'status' => 401,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Clear login attempts on successful login
        LoginAttempt::where('email', $request->email)->delete();



        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 200,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,
        ]);
    }


    public function logout(Request $request)
    {
        $user = auth('sanctum')->user();

        // Check if user exists and has a valid token
        if (!$user || !$user->currentAccessToken()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or missing token.',
            ], 401);
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Logout successful.',
        ]);
    }
}
