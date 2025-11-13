<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaqSection;


class FaqSectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = FaqSection::latest()->get();
        return jsonResponse(true, 'Faq section fetched successfully', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'status'         => 'required|in:Active,Inactive',
        ]);

        $data = FaqSection::create($request->toArray());
        return jsonResponse(true, 'Faq section created successfully', $data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = FaqSection::where('id',$id)->first();
        if (!$data) {
            return jsonResponse(false,'Faq section not found in our database.',$data,404);            
        }

        return jsonResponse(true, 'Faq section fetched successfully', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = FaqSection::where('id',$id)->first();
        if (!$data) {
            return jsonResponse(false,'Faq section not found in our database.',$data,404);   
        }

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'status'         => 'required|in:Active,Inactive',
        ]);


        $data->update($request->toArray());
        return jsonResponse(true, 'Faq section updated successfully', $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $data = FaqSection::where('id',$id)->first();
        if (!$data) {
            return jsonResponse(false,'Faq section not found in our database.',$data,404);   
        }

        $data->delete();
        return jsonResponse(true, 'Faq section deleted successfully');
    }
}
