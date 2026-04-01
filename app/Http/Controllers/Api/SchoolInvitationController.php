<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\SchoolInvitation;
use App\Models\SchoolAggrement;
use App\Mail\School\SchoolInvitationAccept;

class SchoolInvitationController extends Controller
{

    public function update(Request $request, $token)
    {

        $validation = [
            'status' => 'required|in:Accepted,Rejected',
        ];

        $invitation = SchoolInvitation::where('token',$token)
            ->with([
                'school:id,school_name',
            ])
            ->with('user:id,full_name,email')
            ->where('status','Invited')->first();
        if(!$invitation) 
        {
            return jsonResponse(false, 'Invitation not found', null, 404);
        }

        DB::beginTransaction();
        try {

            $invitation->status = $request->status;
            $invitation->token  = Null; 
            $invitation->save();

            if ($request->status === 'Accepted') {
                SchoolAggrement::create([
                    'user_id'   => $invitation->user_id,
                    'school_id' => $invitation->school_id,
                ]);

                $message = 'Invitation accepted successfully. Welcome to the team!';

                // Send mail after success accepted invitation
                $loginLink = env('ADMIN_PANNEL_URL','http://localhost:5173');
                $mailData =[
                    'fullName' => $invitation->user->full_name,
                    'email' => $invitation->user->email,
                    'schoolName' => $invitation->school->school_name,
                    'loginLink' => $loginLink.'/school/login'
                ];

                Mail::to($invitation->user->email)->send(new SchoolInvitationAccept($mailData));
            } 
            else {
                $message = 'Invitation has been declined.';
            }

            DB::commit();
            return jsonResponse(true, $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return jsonResponse(false, $e->getMessage(), null, 500);        
        }



        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $token)
    {
        //
        $invitation = SchoolInvitation::where('token',$token)
            ->where('status','Invited')
            ->with([
                'school:id,school_name,school_slug,address_line_1,address_line_2,city,state,zip,country',
                'school.theme:school_id,logo_image,logo_image_url'   
            ])
            ->with('user:id,full_name')
            ->first();

        if(!$invitation) 
        {
            return jsonResponse(false, 'Invitation not found', null, 404);
        }

        $invitation->invitation_date = formatDisplayDate($invitation->created_at);
        return jsonResponse(true, 'Fetch data', [
            'invitation' => $invitation
        ]);
    }

 
}
