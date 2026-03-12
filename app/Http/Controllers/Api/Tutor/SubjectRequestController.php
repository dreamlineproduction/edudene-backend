<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Models\SubjectRequest;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class SubjectRequestController extends Controller
{
    public function store(Request $request) {
		$validator = Validator::make($request->all(),[
			'category_id' => 'required',
			'sub_category_id' => 'required',
			'sub_sub_category_id' => 'required',
			'subject' => 'required',
		]);


		if ($validator->fails()) {
			return jsonResponse(false, 'Fix validation errors.', ['errors' => $validator->errors()], 400);
		}

		$userId = auth('sanctum')->user()->id;

		$model = new SubjectRequest();
		$model->category_id = $request->category_id;
		$model->sub_category_id = $request->sub_category_id;
		$model->sub_sub_category_id = $request->sub_sub_category_id;
		$model->subject = $request->subject;
		$model->user_id = $userId;
		$model->status = 'Pending';
		$model->save();
		
		$setting = WebsiteSetting::find(1);

		// Send email to admin
		$id = auth('sanctum')->user()->id;

		$tutor = User::find($id);

		//print_r($tutor);

		Mail::to($setting->system_email)->send(new \App\Mail\SubjectRequestToAdmin($tutor, $model));

		return jsonResponse(
			true, 
			'You request has been sent, please wait for admin approval.', 
			[
				'subject' => $model
			],
			200
		);
	}
}
