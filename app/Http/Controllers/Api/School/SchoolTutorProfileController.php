<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SchoolTutorProfileController extends Controller
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
         $loggedInUser = auth('sanctum')->user();

        $tutor = $loggedInUser
            ->school()
            ->with('user:id,full_name,email')
            ->first();

        if (!$tutor) {
            return jsonResponse(false, 'Tutor not found in our database.', null, 404);
        }

        return jsonResponse(true, 'School details', [
            'school' => $tutor
        ]);
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
