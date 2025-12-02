<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\Country;
use Illuminate\Http\Request;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        //
        $states = State::query();

        if(empty($id)){
            return jsonResponse(false,'Country id is required');
        }

        if (is_int($id)) {
            $states->where('country_id',$id);
            $country = Country::find($id);
        } else{
            $country = Country::where('country_name',$id)->first();
        }

        $states = $states->where('country_id',$country->id)
        ->orderBy('name','asc')
        ->get();

        return jsonResponse(true,'Fetch State data',['states' => $states]);
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
