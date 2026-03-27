<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolInvitation;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\School\SchoolInvitationMail;


class SchoolInvitationController extends Controller
{

    public function sendInvitation(Request $request)
    {
        $loggedInUser = auth('sanctum')->user();
        $school = $loggedInUser->school()->first();

        $request->validate([
            'teacher_id' => 'required|exists:users,id',
        ]);

        // Check user exists
        $user = User::where('id', $request->teacher_id)->first();

        $existing = SchoolInvitation::where('school_id', $school->id)
            ->where('user_id', $user->id)
            ->where('status', 'Invited')
            ->first();

        if ($existing) {
            return jsonResponse(false, 'Invitation already sent to this email');
        }

        $token = Str::random(55);

        DB::beginTransaction();
        try {
            // Create invitation
            $invitation = SchoolInvitation::create([
                'school_id'   => $school->id,
                'user_id'     => $user->id,
                'email'       => $user->email,
                'token'       => $token,
                'status'      => 'Invited',
            ]);

            $inviteLink = env('FRONTEND_URL').'/schools/invitation?invite_id='.$token;

            $mailData = [
                'fullName' => $user->full_name,
                'schoolName' => $school->school_name,
                'inviteLink' => $inviteLink
            ];

            //Send email
           
            Mail::to($user->email)->send(new SchoolInvitationMail($mailData));

            DB::commit();

            return jsonResponse(true, 'Invitation sent successfully', [
                'invitation' => $invitation
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(false, $e->getMessage(), null, 500);        
        }
    }
}
