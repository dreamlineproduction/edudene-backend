<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data = SubCategory::with('category')->latest()->get();
        return jsonResponse(true, 'Categories level two fetched successfully', ['categories' => $data]);        
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:200',
			'slug' => 'required',
            'status' => 'required|in:Active,Inactive',
        ]);

        $request->merge([
            'slug' => generateUniqueSlug($request->title,'App\Models\SubCategory'),
        ]);

        $data = SubCategory::create($request->toArray());
        return jsonResponse(true, 'Categories level two created successfully', $data);   
    }

    public function show(string $id)
    {

        $data = SubCategory::where('id', $id)->with('category')->first();

        if (!$data) {
            return jsonResponse(false, 'Categories level two not found in our database.', null, 404);
        }
        
        return jsonResponse(true, 'Categories level two details', ['subCategory' => $data]);
    }
 

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $data = SubCategory::find($id);

        if (!$data) {
            return jsonResponse(false, 'Categories level two not found in our database.',null, 404);               
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:200',
            'status' => 'required|in:Active,Inactive',
        ]);


        $request->merge([
            'slug' => generateUniqueSlug($request->title,'App\Models\SubCategory',$data->id),
        ]);

        $data->update($request->toArray());

        return jsonResponse(true, 'Categories level two updated successfully', $data);        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subCategory = SubCategory::find($id);

        if (!$subCategory) {
            return jsonResponse(false, 'Categories level two not found in our database.',null, 404);               
        }

        $subCategory->delete();

        return jsonResponse(true, 'Categories level two deleted successfully.',null, 200);               
    }
}
