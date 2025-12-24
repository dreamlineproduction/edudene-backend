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
            $session->start_time = formatDisplayDate($session->start_time,'H:i');
            $session->end_time = formatDisplayDate($session->end_time,'H:i');
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
        $loggedInUser = auth('sanctum')->user();

        $request->validate([
            'sessions' => 'required|array',
            'sessions.*.id' => 'required|integer',
            'sessions.*.start_time' => 'required|date_format:H:i',
            'sessions.*.end_time' => 'required|date_format:H:i|after:sessions.*.start_time',
            'sessions.*.topic' => 'nullable|string',
            'sessions.*.is_leave' => 'required|in:Yes,NO',
            'timezone' => 'required|string',
        ]);

        $classes = Classes::where(['id'=>$id])->first();
        if(empty($classes)){
            return jsonResponse(false, 'Class not found in our database', null, 404);
        }

        foreach ($request->sessions as $session) {
            $classesSessions =  ClassSessions::where([
                'id'=> $session['id'],
                'class_id'=> $classes->id])
                ->where(function ($query) use ($loggedInUser) {
                    $query->where('school_id', $loggedInUser->id)
                        ->orWhere('tutor_id', $loggedInUser->id);
                })
                ->first();
            if(!empty($classesSessions)){
                if($session['is_leave'] == 'Yes'){
                    $classesSessions->update([
                        'is_leave' => 'Yes',
                        'topic' => null,
                        'start_time' => null,
                        'end_time' => null,
                    ]);

                } else {
                    $classesSessions->update([                    
                        'start_time' => $session['start_time'],                    
                        'end_time' => $session['end_time'],
                        'topic' => $session['topic'],
                        'is_leave' => $session['is_leave'],
                        'timezone' => $request->timezone,
                    ]);
                }
                
            }
        } 

        return jsonResponse(true, 'Class sessions saved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
