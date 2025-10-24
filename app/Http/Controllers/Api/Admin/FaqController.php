<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $data = Faq::with('section')->latest()->get();

        return jsonResponse(true, 'Faq fetched successfully', $data);
    }
       
 

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'faq_section_id' => 'required|exists:faq_sections,id',
            'title'          => 'required|string|max:255',
            'status'         => 'required|in:Active,Inactive',
            'is_home'        => 'required|in:Yes,No',
        ]);

        $data = Faq::create($request->toArray());
        return jsonResponse(true, 'Faq created successfully', $data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $data = Faq::with('section')->where('id',$id)->first();
        if (!$data) {
            return jsonResponse(true,'Faq not found in our database.',$data,404);            
        }

        return jsonResponse(true, 'Faq fetched successfully', $data);
    }

    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = Faq::where('id',$id)->first();
        if (!$data) {
            return jsonResponse(true,'Faq not found in our database.',$data,404);   
        }

        $validated = $request->validate([
            'faq_section_id' => 'required|exists:faq_sections,id',
            'title'          => 'required|string|max:255',
            'status'         => 'required|in:Active,Inactive',
            'is_home'        => 'required|in:Yes,No',
        ]);


        $data->update($request->toArray());
        return jsonResponse(true, 'Faq updated successfully', $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $data = Faq::where('id',$id)->first();
        if (!$data) {
            return jsonResponse(true,'Faq not found in our database.',$data,404);   
        }

        $data->delete();
        return jsonResponse(true, 'Faq deleted successfully');
    }
}
