<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\ClassSessions;
use Illuminate\Http\Request;

class ClassSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(int $classId = 0)
    {
        $classes =  Classes::where('id', $classId)->with('class_sessions')->first();
        if(empty($classes)){
            return jsonResponse(false, 'Class not found in our database', null, 404);
        }

        $classSessions = $classes->class_sessions;

        $classSessions = collect($classSessions)->map(function ($session) {
            $session->start_date = formatDisplayDate($session->start_date);
            return $session;
        });
        
        return jsonResponse(true, 
            'Class sessions list',
            ['class_sessions' => $classSessions]
        );        
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
