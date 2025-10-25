<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::latest()->get();

        return jsonResponse(true, 'Categories fetched successfully', $categories);
    }

   
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'status' => 'required|in:Active,Inactive',
        ]);
       

        $category = Category::create([
            'title' => $request->title,
            'slug' => generateUniqueSlug($request->title,'App\Models\Category'),
        ]);

        return jsonResponse(true,'Category created successfully',$category);         
    }

  
  

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $category = Category::find($id);

        if (!$category) {
            return jsonResponse(false,'Category not found in our database.',$category,404);            
        }

        $request->validate([
            'title' => 'required|string|max:200',
            'status' => 'required|in:Active,Inactive',
        ]);

        $category->update([
            'title' => $request->title,
            'slug' => generateUniqueSlug($request->title,'App\Models\Category',$category->id),
            'status' => $request->status,
        ]);

        return jsonResponse(true,'Category updated successfully',$category);   
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            if (!$category) {
                return jsonResponse(false,'Category not found in our database.',$category,404);            
            }
        }

        $category->delete();

        return jsonResponse(true,'Category deleted successfully');                   
    }
}
