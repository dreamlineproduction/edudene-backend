<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\User\KycVerificationStatus;
use App\Models\User;
use App\Models\UserVerification;
use App\Models\UserInformation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserVerificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function facialVerification(Request $request)
    {
        $sortBy = $request->get('sort_by', 'title');
    	$sortDirection = $request->get('sort_direction', 'asc');
        $perPage = (int) $request->get('per_page', 10);
    	$page = (int) $request->get('page', 1);


        $query = UserVerification::query();
        $query->select(
            'user_verifications.id', 
            'user_verifications.type', 
            'user_verifications.face_image', 
            'user_verifications.face_image_url', 
            'user_verifications.status',
            'user_verifications.decline_text',
            'user_verifications.created_at',
            'users.full_name',
            'users.email'
        );

        if($request->get('search'))
        {
            $query = $query->where('users.full_name','like','%'.$request->search.'%');
            $query = $query->orWhere('users.email','like','%'.$request->search.'%');
            $query = $query->orWhere('user_verifications.status','like','%'.$request->search.'%');
        }

        if (in_array($sortBy, ['id', 'full_name', 'email', 'status', 'created_at'])) {
			$query = $query->orderBy($sortBy, $sortDirection);
		} else {
			$query = $query->orderByRaw("
                FIELD(user_verifications.status, 'Pending', 'Approved', 'Declined')
            ")->orderBy('user_verifications.created_at', 'desc');
		}

        $query->where('type', 'Face');
        $query->leftJoin('users', 'users.id', '=', 'user_verifications.user_id');
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

		return jsonResponse(true, 'Data fetched successfully', [
			'users' => $paginated->items(),
			'total' => $paginated->total(),
			'current_page' => $paginated->currentPage(),
			'per_page' => $paginated->perPage(),
		]);
    }

    public function showFaceVerification($id)
    {
        //
        $data = UserVerification::select(
            'user_verifications.id', 
            'user_verifications.type', 
            'user_verifications.face_image', 
            'user_verifications.face_image_url', 
            'user_verifications.status',
            'user_verifications.decline_text',
            'user_verifications.created_at',
        )->find($id);

        if(empty($data)) {
            return jsonResponse(false, 'Data not found', [], 404);
        }


        return jsonResponse(true, 'Data fetched successfully', ['user' => $data]);
    }


   

    public function idProofVerification(Request $request)
    {
        $sortBy = $request->get('sort_by', 'title');
    	$sortDirection = $request->get('sort_direction', 'asc');
        $perPage = (int) $request->get('per_page', 10);
    	$page = (int) $request->get('page', 1);


        $query = UserVerification::query();
        $query->select(
            'user_verifications.id', 
            'user_verifications.type', 
            'user_verifications.id_type', 
            'user_verifications.front_side_document', 
            'user_verifications.front_side_document_url', 
            'user_verifications.back_side_document', 
            'user_verifications.back_side_document_url', 
            'user_verifications.status',
            'user_verifications.decline_text',
            'user_verifications.created_at',
            'users.full_name',
            'users.email'
        );

        if($request->get('search'))
        {
            $query = $query->where('users.full_name','like','%'.$request->search.'%');
            $query = $query->orWhere('users.email','like','%'.$request->search.'%');
            $query = $query->orWhere('user_verifications.status','like','%'.$request->search.'%');
        }

        if (in_array($sortBy, ['id', 'full_name', 'email', 'status', 'created_at'])) {
			$query = $query->orderBy($sortBy, $sortDirection);
		} else {
			$query = $query->orderByRaw("
                FIELD(user_verifications.status, 'Pending', 'Approved', 'Declined')
            ")->orderBy('user_verifications.created_at', 'desc');
		}

        $query->where('type', 'IDProof');
        $query->leftJoin('users', 'users.id', '=', 'user_verifications.user_id');
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

		return jsonResponse(true, 'Data fetched successfully', [
			'users' => $paginated->items(),
			'total' => $paginated->total(),
			'current_page' => $paginated->currentPage(),
			'per_page' => $paginated->perPage(),
		]);
    }
    

    public function showIdProofVerification($id)
    {
        //
        $data = UserVerification::select(
            'user_verifications.id', 
            'user_verifications.type', 
            'user_verifications.id_type', 
            'user_verifications.front_side_document', 
            'user_verifications.front_side_document_url', 
            'user_verifications.back_side_document', 
            'user_verifications.back_side_document_url', 
            'user_verifications.status',
            'user_verifications.decline_text',
            'user_verifications.created_at',
        )->find($id);

        if(empty($data)) {
            return jsonResponse(false, 'Data not found', [], 404);
        }


        return jsonResponse(true, 'Data fetched successfully', ['user' => $data]);
    }

    public function update(Request $request, $id)
    {
        $validation = [
            'status' => 'required|in:Approved,Declined',
        ];

        if($request->status === 'Declined'){
            $validation['reason'] = 'required|string|max:255';
        }

        $request->validate($validation);

        
        $data = UserVerification::find($id);
        if(empty($data)) {
            return jsonResponse(false, 'Data not found', [], 404);
        }

        $data->update([
            'status' => $request->status,
            'decline_text' => $request->reason,
        ]);


        if($request->status === 'Approved'){
            $find = ['user_id'=>$data->user_id];

            if($data->type === 'IDProof')
            {
                UserInformation::updateOrCreate($find,[
                    'id_type' => $data->id_type,
                    'front_side_document' => $data->front_side_document,
                    'front_side_document_url' => $data->front_side_document_url,
                    'back_side_document' => $data->back_side_document,
                    'back_side_document_url' => $data->back_side_document_url,
                ]);
            }
            if($data->type === 'Face'){
                UserInformation::updateOrCreate($find,[
                    'face_image' => $data->face_image,
                    'face_image_url' => $data->face_image_url,
                ]);
            }            
        }


        if($request->status === 'Declined'){
            if($data->type === 'IDProof'){
                deleteS3File($data->front_side_document);
                deleteS3File($data->back_side_document);

                $data->update([
                    'front_side_document' => null,
                    'front_side_document_url' => null,
                    'back_side_document'=>null,
                    'back_side_document_url'=>null,
                ]);
            }


            if($data->type === 'Face'){
                deleteS3File($data->face_image);
                $data->update([
                    'face_image' => null,
                    'face_image_url' => null,
                ]);
            }        
        }

        $user = User::where('id', $data->user_id)->first();

        $mailData = [
            'fullName' => $user->full_name,
            'email' => $user->email,
            'status' => $request->status,
            'reason' => $request->reason,
            'kycType' => ($data->type === 'IDProof') ? 'ID Proof' : 'Facial',
        ];

        try{
           Mail::to($user->email)->send(new KycVerificationStatus($mailData)); 
        } catch (\Exception $e) {
            return jsonResponse(false, 'Something went wrong', [], 500);
        }

        return jsonResponse(true, 'Data updated successfully', ['user' => $data]);
    }

}
