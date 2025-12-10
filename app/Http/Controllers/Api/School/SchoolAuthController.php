<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Mail\User\UserEmailVerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SchoolAuthController extends Controller
{
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
