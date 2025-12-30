<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Mail\School\ConfirmationSchoolRegistration;
use App\Mail\User\UserEmailVerificationMail;
use App\Models\LoginAttempt;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SchoolAuthController extends Controller
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

        $user = User::where(['email' => $request->email])->whereIn('role_id',[3,4])->first();

        // Check if user exists
        if (!$user) {
            return jsonResponse(false, 'Account not found in our database.', $user, 404);
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

    /**
     * Display a listing of the resource.
     */
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


        $otpCode =  mt_rand(100000, 999999);

        $request->merge([
            'role_id' => 3,
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'profile_step' => 'required|numeric',
            'school_name' => 'required|string|max:200',
            'phone_number' => 'required|string|max:15',
            'address_line_1' => 'required|string|max:200',
            'city' => 'required|string|max:200',
            'country' => 'required|string|max:200',
            'state' => 'required|string|max:200',
            'zip' => 'required|string|max:200',
            'password' => 'required|string|max:200',
            'owner_name' => 'required|string|max:200',
            'tax_details' => 'required|string|max:200',
            'registration_number' => 'required|string|max:200',
            'year_of_registration' => 'required|digits:4|integer',
            'website' => 'required|string|max:200',
            'social_media' => 'required|string|max:200',
            'license_type' => 'required|string|max:200',
            'school_document' => 'required|integer',
        ]);


        $user = User::where('email', $request->email)->first();
        if(empty($user)){
            return jsonResponse(false, 'User not found.',404);
        }

        //$user = auth('sanctum')->user();
        $userName = generateUniqueSlug($request->owner_name, 'App\Models\User', $user->id, 'user_name');

        // Update userinformation
        $user->update([
            'full_name' => $request->owner_name,
            'user_name' => $userName,
            'password' => Hash::make($request->password),
            'profile_step'=>0,
            'is_profile_complete'=>1
        ]);

        //$user = auth('sanctum')->user();
        $schoolSlug = generateUniqueSlug($request->school_name, 'App\Models\School', $user->id, 'school_slug');

        // Update other profile information
        $request->merge([
            'user_id' => $user->id,
            'school_slug' => $schoolSlug
        ]);

        $newPath = 'schools';
        // Save qualification certificate
        if (notEmpty($request->school_document)) {
            
            $document = finalizeFile($request->school_document,$newPath);


            $request->merge([
                'school_document' => $document['path'],
                'school_document_url' => $document['url']
            ]);
        }

        $school = School::updateOrCreate(['user_id' => $user->id], $request->toArray());

        $mailData = [
            "fullName" => $user->full_name,
            "schoolName" => $school->school_name,
            "loginLink" => env('WEBSITE_URL').'/'.$school->school_slug.'/login',
        ];

        try{
            // Send activation email
            Mail::to($user->email)->send(new ConfirmationSchoolRegistration($mailData));
        } catch (\Exception $e) {
            return jsonResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
        }

        return jsonResponse(true, 'School profile created successfully');
    }

    
}
