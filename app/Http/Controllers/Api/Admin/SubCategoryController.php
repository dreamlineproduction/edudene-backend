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
        return jsonResponse(true, 'Field fetched successfully', ['categories' => $data]);        
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
            'slug' => generateUniqueSlug($request->slug,'App\Models\SubCategory'),
        ]);

        $data = SubCategory::create($request->toArray());
        return jsonResponse(true, 'Field created successfully', $data);   
    }

    public function show(string $id)
    {

        $data = SubCategory::where('id', $id)->with('category')->first();

        if (!$data) {
            return jsonResponse(false, 'Field not found in our database.', null, 404);
        }
        
        return jsonResponse(true, 'Field details', ['subCategory' => $data]);
    }
 

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $data = SubCategory::find($id);

        if (!$data) {
            return jsonResponse(false, 'Field not found in our database.',null, 404);               
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:200',
            'status' => 'required|in:Active,Inactive',
        ]);


        $request->merge([
            'slug' => generateUniqueSlug($request->slug,'App\Models\SubCategory',$data->id),
        ]);

        $data->update($request->toArray());

        return jsonResponse(true, 'Field updated successfully', $data);        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subCategory = SubCategory::find($id);

        if (!$subCategory) {
            return jsonResponse(false, 'Field not found in our database.',null, 404);               
        }

        $subCategory->delete();

        return jsonResponse(true, 'Field deleted successfully.',null, 200);               
    }

	// Fetch Sub Categories for a specific category
	public function getSubCategories($categoryId) {

		if ($categoryId) {

			$subCategories = SubCategory::where("category_id",$categoryId)->get();

			if (!$subCategories) {
				return jsonResponse(false, 'Sub Categories not found.',null, 404);               
			}

			return jsonResponse(true, 'Sub Categories fetched successfully.',['subCategories' => $subCategories], 200);   
		}

		return jsonResponse(false, 'Sub Categories not found.',null, 404);               

	}
}
