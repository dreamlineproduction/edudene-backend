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
        $query = User::query()
            ->select('users.id','users.role_id','users.full_name','school_aggrements.is_freelancer')
            ->leftJoin('school_aggrements','school_aggrements.user_id','=','users.id')
            ->where(function($q){
                $q->where('users.role_id',2)
                ->orWhere(function($q2)  {
                    $q2->where('users.role_id',4)
                    ->where('school_aggrements.is_freelancer', 'Yes');
                });
            })
            ->orderBy('full_name','asc');

        $tutors =  $query->get();

        return jsonResponse(true,'Tutor data',['tutors' => $tutors]);
    }

    
}
