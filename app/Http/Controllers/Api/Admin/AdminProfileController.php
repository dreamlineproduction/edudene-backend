<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Admin;
use App\Models\User;


class AdminProfileController extends Controller
{
    //
    public function show()
    {
        
        $loggedInUser = auth('sanctum')->user();

        $admin = $loggedInUser
            ->admin()
            ->first();

        if (!$admin) {
            return jsonResponse(false, 'Admin not found in our database.', null, 404);
        }

        $data['admin'] =  array_merge(
            $loggedInUser->only(['id', 'role_id', 'full_name','email','user_name']),
            $admin->toArray()
        );


        return jsonResponse(true, 'Admin details', $data);
    }

    public function update(Request $request)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return jsonResponse(false, 'Unauthorized', null, 401);
        }

        $admin = $user->admin()->first();

        $request->validate([
            'user_name' => 'required|string|max:100',
            'full_name' => 'required|string|max:200',
            'email' => [
                'required',
                'email',
                'max:200',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'user_name' => [
                'required',
                'max:200',
                Rule::unique('users', 'user_name')->ignore($user->id),
            ],


            'address_line_1' => 'required|string|max:200',
            'address_line_2' => 'nullable|string|max:200',

            'phone_number' => 'required|string|max:15',

            'city' => 'required|string|max:200',
            'state' => 'required|string|max:200',
            'country' => 'required|string|max:200',
            'zip' => 'required|string|max:20',


            // Social links
            'facebook_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',
            'x_url' => 'nullable|url',

            // Image
            'avatar' => 'nullable|integer',
        ]);

        $user->update([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'user_name' => $request->user_name,
        ]);

        $createData = $request->only([
            'address_line_1',
            'address_line_2',
            'phone_number',
            'city',
            'state',
            'country',
            'zip',
            'facebook_url',
            'linkedin_url',
            'instagram_url',
            'youtube_url',
            'x_url',
        ]);

        $createData['user_id'] = $user->id;

        /** Handle logo upload */
        if ($request->filled('avatar')) {

            if ($admin && $admin->avatar) {
                deleteS3File($admin->avatar);
            }

            $image = finalizeFile($request->avatar, 'admins');

            $createData['avatar'] = $image['path'];
            $createData['avatar_url'] = $image['url'];
        }

        /** Create or update school */
        $admin = Admin::updateOrCreate(
            ['user_id' => $user->id],
            $createData
        );

        /** Clean response */
        $data['admin'] = array_merge(
            $user->only(['id','role_id','full_name','email','user_name']),
            $admin->toArray()
        );

        return jsonResponse(true, 'Profile has been updated successfully', $data);
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



}
