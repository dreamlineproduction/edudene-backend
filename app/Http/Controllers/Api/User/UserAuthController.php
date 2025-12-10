<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Mail\SentOtpToMail;
use App\Mail\User\UserEmailVerificationMail;
use App\Mail\User\UserPasswordResetMail;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    // Register API
    public function register(Request $request)
    {
        $validation = [
            'timezone' => 'required|string|max:150'
        ];


        $validation['email'] = 'required|string|email|max:200';

        if(empty($request->resend)){
            $user = User::where('email',$request->email)->exists();
            if($user){
                return jsonResponse(false, 'The email has already been taken.');
            }
            $responseMessage = 'Registration successfull! Please check your email to verify your account.';
        } else {
            $responseMessage = 'A new opt code has been sent to your email. Please verify your account.';
        }

        

        $request->validate($validation);

      


        // Create activation token
        //$activationToken = Str::random(90);

        $otpCode =  mt_rand(100000, 999999);

        $request->merge([
            'role_id' => 1,
            'remember_token' => $otpCode,
            'status' => 'Active',
            'timezone' => getDefaultTimezone($request->timezone),
        ]);


        $find = ['email' => $request->email];
        $user = User::updateOrCreate($find, $request->toArray());
        //$activationLink = url('/user/verify-account?token=' . $activationToken);

        $mailData = [
            'mail' => $user->email,
            'otpCode' => $otpCode,
        ];

        // Send activation email
        //Mail::to($user->email)->send(new UserEmailVerificationMail($mailData));

        return jsonResponse(true, $responseMessage);
    }


    // Login API
    public function login(Request $request)
    {
        $validation = [
            'login_with'=>'required|in:PASSWORD,OTP',
            'email' => 'required|email',
            'timezone' => 'nullable|string|max:150',
            'login_datetime' => 'nullable|string|max:150'
        ];

        // Add validation
        if($request->login_with === 'PASSWORD')
        {
            $validation['password'] = 'required|string';
        }

        if($request->login_with === 'OTP')
        {
            $validation['otp'] = 'required|string|min:6|max:6'; 
        }


        $request->validate($validation);


        $user = User::where(['email' => $request->email, 'role_id' => 1])->first();

        // Check if user exists
        if (!$user) {
            return jsonResponse(false, 'User not found in our database.', null, 404);
        }

        if($request->login_with === 'OTP'){
            if($user->remember_token !== $request->otp){
                return jsonResponse(false, 'Invalid opt code.', null, 400); 
            }          
        }
        

      

        // Check if user is temporarily inactive due to too many failed login attempts
        if ($user->temporary_status === 'Inactive') {
            return jsonResponse(false, 'Your account is temporarily inactive due to too many failed login attempts.Please try again later or contact support.', [], 400);
        }

        // Check if user is inactive
        if ($user->status === 'Inactive') {
            return jsonResponse(false, 'Your account is inactive. Please activate your account.', [], 400);
        }

        // Check password
        if ($request->login_with === 'PASSWORD' && !Hash::check($request->password, $user->password)) {

            $loginAttempt =  LoginAttempt::where('email', $request->email)->first();

            // If attempts exceed 5, user account temporary status Inactive.
            if (!empty($loginAttempt) && $loginAttempt->attempt_count >= 5) {

                $user->temporary_status = 'Inactive';
                $user->save();

                return jsonResponse(false, 'Too many login attempts. Please try again later.', [], 400);
            }

            // Log the failed login attempt
            $find = ['email' => $request->email];
            LoginAttempt::updateOrCreate($find, [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'email' => $request->email,
                'attempt_count' => notEmpty($loginAttempt) ? $loginAttempt->attempt_count  + 1 : 1,
                'locked_datetime' => $request->login_datetime ?? now(),
                'timezone' => getDefaultTimezone($request->timezone),
            ]);

            return jsonResponse(true, 'Sorry, your password was incorrect. Please double-check your password.', [], 400);
        }

        // Clear login attempts on successful login
        LoginAttempt::where('email', $request->email)->delete();


        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        $data['user'] = $user;
        $data['token'] = $token;
        return jsonResponse(true, 'Login successfully.', $data);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email_or_username' => 'required|string|max:255',
        ]);

        if(filter_var($request->email_or_username, FILTER_VALIDATE_EMAIL)){
            $where = ['email' => $request->email_or_username];  
        } else {
            $where = ['user_name' => $request->email_or_username];
        }



        $user = User::where($where)->first();
        if(empty($user)){
            return jsonResponse(false, 'User not found in our database.', null, 404);
        }
        
        try{
            
            $resetToken = Str::random(90);

            $user->update([
                'remember_token' => $resetToken,
            ]);

            //$resetLink = url('/user/reset-password?token=' . $resetToken);
            $resetLink = env('WEBSITE_URL');
            $resetLink .= 'user/reset-password?token=' . $resetToken;

            $mailData = [
                'fullName' => $user->full_name,
                'mail' => $user->email,
                'resetLink' => $resetLink,
            ];

            // Send password reset email
            Mail::to($user->email)->send(new UserPasswordResetMail($mailData));

            return jsonResponse(true, 'Password reset link has been sent to your email.');
        } catch (\Exception $e) {
            return jsonResponse(false, 'An error occurred: ' . $e->getMessage(), [], 500);
        }        
    }

    public function checkIsValidToken(Request $request)
    {
        if(empty($request->token)){
            return jsonResponse(false, 'Token is required.', null, 400);
        }

        $user = User::where('remember_token', $request->token)->first();

        if(empty($user)){
            return jsonResponse(false, 'Invalid or expired token.', null, 400);
        }        

        // $user->update([
        //     'remember_token' => NULL,
        // ]);

        $response['email'] = $user->email;
        $response['full_name'] = $user->full_name;
        return jsonResponse(true, 'Valid token.',['user' => $response]);        
    } 
    
    public function updatePassword(Request $request)
    {
        $request->validate([
            'email'=>'required|string|email|max:255',
            'token' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where(['remember_token'=>$request->token, 'email'=>$request->email])->first();

        if (!$user) {
            return jsonResponse(false, 'Invalid token.', null, 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'remember_token' => NUll,
        ]);
        return jsonResponse(true, 'Password has been updated successfully.');
    }

    public function logout(Request $request)
    {
        try {
            $user = auth('sanctum')->user();

            // Check if user exists and has a valid token
            if (!$user || !$user->currentAccessToken()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or missing token.',
                ], 401);
            }


            $user->currentAccessToken()->delete();

            return jsonResponse(true, 'Logout successful.');
        } catch (\Exception $e) {
            return jsonResponse(false, 'An error occurred during logout: ' . $e->getMessage(), [], 500);
        }
    }


    public function sendOtpToEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        try{

            $otp = mt_rand(10000, 99999);
            $user->update([
                'remember_token' => $otp,
            ]);

            $mailData = [
                'fullName' => $user->full_name,
                'mail' => $user->email,
                'otp' => $otp,
            ];

            // Send password reset email
            Mail::to($user->email)->send(new SentOtpToMail($mailData));

            return jsonResponse(true, 'OTP has been sent to your email.');
        } catch (\Exception $e) {
            return jsonResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|exists:users,email',
            'otp' => 'required|integer',
        ]);

        $user = User::where(['email'=>$request->email,'remember_token' => $request->otp])->first();

        if($user){
            $user->update([
                'remember_token' => Null,
                'profile_step'=>1
            ]);

            $data['role'] = $user->role_id;
            $data['full_name'] = $user->full_name;
            return jsonResponse(true, 'OTP is valid.',['user' => $data]);
        } else{
            return jsonResponse(false, 'OTP is not valid.',null, 400);
        }
    }
}
