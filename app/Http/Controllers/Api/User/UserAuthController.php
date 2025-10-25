<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Mail\User\UserEmailVerificationMail;
use App\Mail\UserAccountActivationMail;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserBillingInformation;
use App\Models\UserCategory;
use App\Models\UserInformation;
use App\Models\UserQualification;
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

        if ($request->resend === true) {
            $validation['email'] = 'required|string|email|max:255|exists:users,email';
        } else {
            $validation['email'] = 'required|string|email|max:255|unique:users';
        }

        $request->validate($validation);

        // Create activation token
        $activationToken = Str::random(90);

        $request->merge([
            'role_id' => 1,
            'remember_token' => $activationToken,
            'timezone' => getDefaultTimezone($request->timezone),
        ]);


        $find = ['email' => $request->email];
        $user = User::updateOrCreate($find, $request->toArray());
        $activationLink = url('/user/verify-account?token=' . $activationToken);

        $mailData = [
            'mail' => $user->email,
            'activationLink' => $activationLink,
        ];

        // Send activation email
        Mail::to($user->email)->send(new UserEmailVerificationMail($mailData));

        return jsonResponse(true, 'Registration successful! Please check your email to verify your account.');
    }

    // Save Basic Information`
    public function saveBasicInformation(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'gender' => 'required|string|in:Male,Female,Other',
            'date_of_birth' => 'required|date',
        ]);


        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return jsonResponse(false, 'User not found.', null, 404);
        }

        $userName = generateUniqueSlug($request->full_name, 'App\Models\User', $user->id, 'user_name');

        // Update userinformation
        $user->update([
            'full_name' => $request->full_name,
            'user_name' => $userName,
        ]);


        // Update other profile information
        $request->merge([
            'user_id' => $user->id,
        ]);

        $find = ['user_id' => $user->id];
        UserInformation::updateOrCreate($find, $request->toArray());

        // Save other profile information as needed
        return jsonResponse(true, 'Profile basic information saved successfully.');
    }

    // Save Education Qualification
    public function saveEducationQualification(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'qualifications' => 'required|array|min:1',
            // 'qualifications.*.qualification_name' => 'required|string|max:255',
            // 'qualifications.*.institution_name' => 'required|string|max:255',
            // 'qualifications.*.completion_year' => 'required|digits:4',
            // 'qualifications.*.is_show_profile' => 'required|boolean',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return jsonResponse(false, 'User not found.', null, 404);
        }
      
        // Delete existing qualifications
        UserQualification::where('user_id', $user->id)->delete();

        // Save all
        foreach ($request->qualifications as $qualification) {
            // if (empty($qualification['qualification_name']) || empty($qualification['institution_name']) || empty($qualification['completion_year'])) {
            //     continue;
            // }

            UserQualification::create([
                'user_id' => $user->id,
                'qualification_name' => $qualification['qualification_name'],
                'institution_name' => $qualification['institution_name'],
                'completion_year' => $qualification['completion_year'],
                'is_show_profile' => $qualification['is_show_profile'],
            ]);
        }

        // Implement the logic to save education qualification
        return jsonResponse(true, 'Education qualification saved successfully.');
    }

    // Save Social Links
    public function saveSocialLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'x_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return jsonResponse(false, 'User not found.', null, 404);
        }

        $find = ['user_id' => $user->id];
        UserInformation::updateOrCreate($find, $request->toArray());
        return jsonResponse(true, 'Social links saved successfully.');
    }


    // Save billing Information
    public function bilingInformation(Request $request)
    {
        // Implement the logic to save billing information
        $request->validate([
            'email' => 'required|email',
            'billing' => 'required|array|min:1',
            'billing.*.address_type' => 'required|string|max:150',
            'billing.*.address_line_1' => 'required|string|max:200',
            'billing.*.city' => 'required|string|max:150',
            'billing.*.state' => 'required|string|max:150',
            'billing.*.zip' => 'required|string|max:20',
            'billing.*.country' => 'required|string|max:150',
            'billing.*.set_default' => 'required|boolean',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return jsonResponse(false, 'User not found.', null, 404);
        }

        // Delete existing qualifications
        UserBillingInformation::where('user_id', $user->id)->delete();

        // Save all
        foreach ($request->billing as $row) {
            // if (empty($row['address_type']) || empty($row['address_line_1']) ||
            //     empty($row['city']) ||
            //     empty($row['state']) ||
            //     empty($row['zip']) ||
            //     empty($row['country'])) {
            //     continue;
            // }

            UserBillingInformation::create([
                'user_id' => $user->id,
                'address_type' => $row['address_type'],
                'address_line_1' => $row['address_line_1'],
                'address_line_2' => $row['address_line_2'],
                'city' => $row['city'],
                'state' => $row['state'],
                'country' => $row['country'],
                'zip' => $row['zip'],
                'set_default' => $row['set_default'],
            ]);
        }

        return jsonResponse(true, 'Education qualification saved successfully.');
    }

    // Save How Did You Find Us
    public function saveFoundUs(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'found_us' => 'nullable|max:100',          
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return jsonResponse(false, 'User not found.', null, 404);
        }
       
        
        $find = ['user_id' => $user->id];
        UserInformation::updateOrCreate($find, [
            'found_us' => $request->found_us,
        ]);

        return jsonResponse(true, 'Saved successfully.');
    }


    // Save 
    public function saveUserCategory(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer'],   
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return jsonResponse(false, 'User not found.', null, 404);
        }

        try {
            // Delete existing categories
            UserCategory::where('user_id', $user->id)->delete();
            foreach ($request->categories as $categoryId) {

                if(empty($categoryId)){
                    continue;
                }

                UserCategory::create([
                    'user_id' => $user->id,
                    'category_id' => $categoryId,
                ]);
            }

            return jsonResponse(true, 'User category saved successfully.');
        } catch (\Exception $e) {
            return jsonResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
        }        
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'timezone' => 'nullable|string|max:150',
            'login_datetime' => 'nullable|string|max:150',
        ]);

        $user = User::where(['email' => $request->email, 'role_id' => 1])->first();

        // Check if user exists
        if (!$user) {
            return jsonResponse(false, 'User not found in our database.', $user, 404);
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

            return jsonResponse(true, 'Sorry, your password was incorrect. Please double-check your password.', [], 401);
        }

        // Clear login attempts on successful login
        LoginAttempt::where('email', $request->email)->delete();


        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        $data['user'] = $user;
        $data['token'] = $token;
        return jsonResponse(true, 'Login successfully.', $data);
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
}
