<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\UserVerification;
use Illuminate\Http\Request;

class UserVerificationController extends Controller
{
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


        $data['id_proof'] =  UserVerification::create($createArray);
        return jsonResponse(true, 'Id proof document saved successfully.',$data);  
    }

    /**
     * Display the specified resource.
     */
    public function faceVerification(Request $request)
    {
        //
        $request->validate([
            'image_id' => 'required|integer',
        ]);  

        $user = auth('sanctum')->user();

        $newPath = 'users/'.$user->id;

        $createArray = [
            'user_id' => $user->id,
            'type' => 'Face',
            'status' => 'Pending',
        ];

        if(notEmpty($request->image_id)) 
        {
            $file = finalizeFile($request->image_id,$newPath);
            $createArray = array_merge($createArray,[
                'face_image' => $file['path'],
                'face_image_url' => $file['url']
            ]);
        }


        $data['last_face_proof'] =  UserVerification::create($createArray);
        return jsonResponse(true, 'Face image saved successfully.',$data);  
    }    
}
