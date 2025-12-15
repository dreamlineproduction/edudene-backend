<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\UserVerification;
use Illuminate\Http\Request;

class UserVerificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function kyc(Request $request)
    {
        //
        $request->validate([
            'id_type' => 'required',
            'front_side_document' => 'required',
            'back_side_document' => 'required',
        ]);  

        $user = auth('sanctum')->user();

       

        $newPath = 'users/'.$user->id;

        $createArray = [
            'user_id' => $user->id,
            'type' => 'IDProof',
            'status' => 'Pending',
        ];

        if(notEmpty($request->front_side_document)) 
        {
            $file = finalizeFile($request->front_side_document,$newPath);
            $createArray = array_merge($createArray,[
                'front_side_document' => $file['path'],
                'front_side_document_url' => $file['url']
            ]);
        }

        if(notEmpty($request->back_side_document)) 
        {
            $file = finalizeFile($request->back_side_document,$newPath);
            $createArray = array_merge($createArray,[
                'back_side_document' => $file['path'],
                'back_side_document_url' => $file['url']
            ]);
        }


        $data['id_proof'] =  UserVerification::updateOrCreate(['user_id' => $user->id],$createArray);
        return jsonResponse(true, 'Id proof document saved successfully.',$data);  
    }

    /**
     * Display the specified resource.
     */
    public function face(string $id)
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
