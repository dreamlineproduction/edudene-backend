<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use Illuminate\Http\Request;

class TutorProfileController extends Controller
{
    public function show()
	{
		$id =  auth('sanctum')->user()->id;
		$tutor = Tutor::where('user_id', $id)->first();
		return jsonResponse(true, 'Tutor found', ['tutor' => $tutor]);
	}
}
