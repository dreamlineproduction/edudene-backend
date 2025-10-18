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
        return jsonResponse(true, 'Sub category fetched successfully', $data);        
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:200',
            'status' => 'required|in:Active,Inactive',
        ]);

        $request->merge([
            'slug' => generateUniqueSlug($request->title,'App\Models\SubCategory'),
        ]);

        $data = SubCategory::create($request->toArray());
        return jsonResponse(true, 'Subcategory created successfully', $data);   
    }

 

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $data = SubCategory::find($id);

        if (!$data) {
            return jsonResponse(true, 'Subcategory not found in our database.', [], 404);               
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

        return jsonResponse(true, 'SubCategory updated successfully', $data);        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subCategory = SubCategory::find($id);

        if (!$subCategory) {
            return jsonResponse(true, 'Subcategory not found in our database.', [], 404);               
        }

        $subCategory->delete();

        return jsonResponse(true, 'Subcategory deleted successfully.', [], 200);               
    }
}
