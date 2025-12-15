<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\User\ConfirmationUserRegistration;

use App\Models\UserBillingInformation;
use App\Models\UserCategory;
use App\Models\UserInformation;
use App\Models\UserQualification;
use App\Models\User;
use Google\Service\BigQueryDataTransfer\UserInfo;
use Illuminate\Support\Facades\Mail;

class UserProfileController extends Controller
{
    //104|edudene_token_K9jgUqsdlWZktaUFa50an4HiNsYITcq1Vwp7hbLB47fd17a9

    
    public function show()
    {
        $loggedInUser = auth('sanctum')->user();

        $user = User::find($loggedInUser->id);

        $user->makeHidden([
            'role',
            'information',
            'qualification',
            'billingInformation',
            'categories',
            'course',
            'lastEmailChangeRequest',
            'lastIdProof',
            'lastFaceProof',
        ]);

        $data['user']  = $user;
        $data['role']  = $user->role;
        $data['information']  = $user->information;
        $data['qualification']  = $user->qualification;
        $data['billingInformation']  = $user->billingInformation;
        $data['categories']  = $user->categories;
        $data['course']  = $user->course;
        $data['last_email_change_request']  = $user->lastEmailChangeRequest;
        $data['last_id_proof']  = $user->lastIdProof;
        $data['last_face_proof']  = $user->lastFaceProof;
        $data['classes']  = [];        

        
        return jsonResponse(true, 'Profile information', $data);
    }

    public function savePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'confirm_password' => 'required|string|same:password',
        ]);

        $user = auth('sanctum')->user();

        $user->update([
            'password' => Hash::make($request->password),
            'profile_step' => 2,
        ]);

        return jsonResponse(true, 'Password has been updated successfully.');
    }

    // Save Basic Information`
    public function saveBasicInformation(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'gender' => 'required|string|in:Male,Female,Other',
            'date_of_birth' => 'required|date',
        ]);


        $user = auth('sanctum')->user();

        $userName = generateUniqueSlug($request->full_name, 'App\Models\User', $user->id, 'user_name');

        // Update userinformation
        $user->update([
            'full_name' => $request->full_name,
            'user_name' => $userName,
            'profile_step'=>3
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
            'qualifications' => 'required|array|min:1',
            // 'qualifications.*.qualification_name' => 'required|string|max:255',
            // 'qualifications.*.institution_name' => 'required|string|max:255',
            // 'qualifications.*.completion_year' => 'required|digits:4',
            // 'qualifications.*.is_show_profile' => 'required|boolean',
        ]);

        $user = auth('sanctum')->user();
        
        $user->update([
            'profile_step'=>4
        ]);

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
            'x_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
        ]);

        $user = auth('sanctum')->user();

        $user->update([
            'profile_step'=>5
        ]);

        $find = ['user_id' => $user->id];
        UserInformation::updateOrCreate($find, $request->toArray());
        return jsonResponse(true, 'Social links saved successfully.');
    }


    // Save billing Information
    public function bilingInformation(Request $request)
    {
        // Implement the logic to save billing information
        $request->validate([
            'billing' => 'required|array|min:1',
            'billing.*.address_type' => 'required|string|max:150',
            'billing.*.address_line_1' => 'required|string|max:200',
            'billing.*.city' => 'required|string|max:150',
            'billing.*.state' => 'required|string|max:150',
            'billing.*.zip' => 'required|string|max:20',
            'billing.*.country' => 'required|string|max:150',
            'billing.*.set_default' => 'required|boolean',
        ]);

        $user = auth('sanctum')->user();

        $user->update([
            'profile_step'=>6
        ]);
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
            'found_us' => 'nullable|max:100',      
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer'],       
        ]);

        $user = auth('sanctum')->user();
             
        $user->update([
            'profile_step'=>0,
            'is_profile_complete' => 1
        ]);
               
        $find = ['user_id' => $user->id];
        UserInformation::updateOrCreate($find, [
            'found_us' => $request->found_us,
        ]);

        $mailData = [
            'fullName' => $user->full_name,
            'loginLink' => env('WEBSITE_URL').'/login',
        ];

        try{
            Mail::to($user->email)->send(new ConfirmationUserRegistration($mailData));
        } catch (\Exception $e) {
            return jsonResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
        }

        // try {
        //     // Delete existing categories
        //     UserCategory::where('user_id', $user->id)->delete();
        //     foreach ($request->categories as $categoryId) {

        //         if(empty($categoryId)){
        //             continue;
        //         }

        //         UserCategory::create([
        //             'user_id' => $user->id,
        //             'category_id' => $categoryId,
        //         ]);
        //     }           
        // } catch (\Exception $e) {
        //     return jsonResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
        // } 

        return jsonResponse(true, 'Saved successfully.');
    }

    // Save 
    public function saveUserCategory(Request $request)
    {
        $request->validate([
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer'],   
        ]);

        $user = auth('sanctum')->user();
        
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

    // public function kyc(Request $request)
    // {
              
    // }



}
