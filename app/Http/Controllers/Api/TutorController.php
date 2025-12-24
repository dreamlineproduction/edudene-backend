<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TutorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($roleId = 2)
    {
        //
        $query = User::query()
            ->where('role_id',$roleId)
            ->orderBy('full_name','asc');

        $tutors =  $query->get();

        return jsonResponse(true,'Tutor data',['tutors' => $tutors]);
    }

    
}
