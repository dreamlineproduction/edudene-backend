<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubSubCategory;
use Illuminate\Http\Request;

class SubSubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = SubSubCategory::with(['category', 'subcategory'])->latest()->get();
        return jsonResponse(true, 'Sub Sub Categories fetched successfully.', $data);         
    }

   
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'title' => 'required|string|max:150',
            'status' => 'required|in:Active,Inactive',
        ]);

        $request->merge([
            'slug' => generateUniqueSlug($request->title,'App\Models\SubSubCategory'),
        ]);

        $data = SubSubCategory::create($request->toArray());
        return jsonResponse(true, 'Sub Sub Category created successfully.', $data);
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
        $data = SubSubCategory::find($id);

        if (!$data) {
            return jsonResponse(true, 'Sub Sub Category not found in our database.', [], 404);               
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'title' => 'required|string|max:150',
            'status' => 'required|in:Active,Inactive',
        ]);

        $request->merge([
            'slug' => generateUniqueSlug($request->title,'App\Models\SubSubCategory',$data->id),
        ]);

        $data->update($request->toArray());

        return jsonResponse(true, 'Sub Sub Category updated successfully.', $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $data = SubSubCategory::find($id);

        if (!$data) {
            return jsonResponse(true, 'Sub Sub Category not found in our database.', [], 404);               
        }

        $data->delete();
        return jsonResponse(true, 'Sub Sub Category deleted successfully.');
    }
}
