<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Mail\User\UserEmailVerificationMail;
use Illuminate\Support\Facades\Hash;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TutorAuthController extends Controller
{
    // Login API
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'timezone' => 'nullable|string|max:150',
            'login_datetime' => 'nullable|string|max:150',
        ]);

        $user = User::where(['email' => $request->email, 'role_id' => 2])->first();

        // Check if user exists
        if (!$user) {
            return jsonResponse(false, 'Tutor not found in our database.', $user, 404);
        }

        // Check if user is temporarily inactive due to too many failed login attempts
        if ($user->temporary_status === 'Inactive') {
            return jsonResponse(false, 'Your account is temporarily inactive due to too many failed login attempts.Please try again later or contact support.', [], 423);
        }

        // Check if user is inactive
        if ($user->status === 'Inactive') {
            return jsonResponse(false, 'Your account is inactive. Please activate your account.', [], 403);
        }

        // Check password
        if (!Hash::check($request->password, $user->password)) {

            $loginAttempt =  LoginAttempt::where('email', $request->email)->first();

            // If attempts exceed 5, user account temporary status Inactive.
            if (!empty($loginAttempt) && $loginAttempt->attempt_count >= 5) {

                $user->temporary_status = 'Inactive';
                $user->save();

                return jsonResponse(false, 'Too many login attempts. Please try again later.', [], 429);
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
            'role_id' => 2,
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

        try{
            // Send activation email
            Mail::to($request->email)->send(new UserEmailVerificationMail($mailData));
        } catch (\Throwable $th) {
            return jsonResponse(false, $th->getMessage());
        }
        

        return jsonResponse(true, $responseMessage);
    }

    public function saveBasicInfo(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'full_name' => 'required|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();
        if(empty($user)){
            return jsonResponse(false, 'User not found.',404);
        }

        //$user = auth('sanctum')->user();
        $userName = generateUniqueSlug($request->full_name, 'App\Models\User', $user->id, 'user_name');

        // Update userinformation
        $user->update([
            'full_name' => $request->full_name,
            'user_name' => $userName,
            'profile_step'=>2
        ]);


        // Update other profile information
        $request->merge([
            'user_id' => $user->id,
        ]);

        $find = ['user_id' => $user->id];

        Tutor::updateOrCreate($find, $request->toArray());


        //UserInformation::updateOrCreate($find, $request->toArray());

        // Save other profile information as needed
        return jsonResponse(true, 'Profile basic information saved successfully.');

    }

    public function setPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string|max:12',
        ]);

        $user = User::where('email', $request->email)->first();
        if(empty($user)){
            return jsonResponse(false, 'User not found.',404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return jsonResponse(true, 'Password has been updated successfully.');
    }

    public function saveDocument(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:200',
            'university' => 'required|string|max:200',
            'highest_qualification' => 'required|string|max:200',
        ]);

        $user = User::where('email', $request->email)->first();
        if(empty($user)){
            return jsonResponse(false, 'User not found.',404);
        }      

        $find = ['user_id' => $user->id];
        Tutor::updateOrCreate($find, $request->toArray());

        return jsonResponse(true, 'Document has been updated successfully.');
    }
}
