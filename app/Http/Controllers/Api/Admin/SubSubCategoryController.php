<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryLevelFour;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use Illuminate\Http\Request;

class SubSubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
		$subSubCategories = SubSubCategory::query();

		if (!empty($request->search)) {
			$subSubCategories = $subSubCategories->where('title','like','%'.$request->search.'%');
		}

		$sortBy = $request->get('sort_by', 'title');
    	$sortDirection = $request->get('sort_direction', 'asc');

		if (in_array($sortBy, ['id', 'title', 'slug', 'status', 'created_at'])) {
			$subSubCategories = $subSubCategories->orderBy($sortBy, $sortDirection);
		} else {
			$subSubCategories = $subSubCategories->orderBy('title', 'asc');
		}

		$perPage = (int) $request->get('per_page', 10);
    	$page = (int) $request->get('page', 1);

		$paginated = $subSubCategories->with(['category', 'categoryLevelTwo'])
						->paginate($perPage, ['*'], 'page', $page);

		 return jsonResponse(true, 'Course fetched successfully.', [
			'subSubCategories' => $paginated->items(),
			'total' => $paginated->total(),
			'current_page' => $paginated->currentPage(),
			'per_page' => $paginated->perPage(),
		]);        
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
            'slug' => generateUniqueSlug($request->slug,'App\Models\SubSubCategory'),
        ]);

        $data = SubSubCategory::create($request->toArray());
        return jsonResponse(true, 'Course created successfully.', $data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $data = SubSubCategory::where('id',$id)->with(['category', 'categoryLevelTwo'])->first();

        if (!$data) {
            return jsonResponse(false, 'Course not found in our database.', null, 404);
        }
        
        return jsonResponse(true, 'Course details.', ['subSubCategory' => $data]);
    }

 

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = SubSubCategory::find($id);

        if (!$data) {
            return jsonResponse(false, 'Course not found in our database.', [], 404);               
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'title' => 'required|string|max:150',
            'status' => 'required|in:Active,Inactive',
        ]);

        $request->merge([
            'slug' => generateUniqueSlug($request->slug,'App\Models\SubSubCategory',$data->id),
        ]);

        $data->update($request->toArray());

        return jsonResponse(true, 'Course updated successfully.', $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $data = SubSubCategory::find($id);

        if (!$data) {
            return jsonResponse(true, 'Course not found in our database.', null, 404);               
        }

        $data->delete();
        return jsonResponse(true, 'Course deleted successfully.');
    }

	// Fetch Sub Sub Categories for a specific category
	public function getSubSubCategories($categoryId) {

		if ($categoryId) {

			$subSubCategories = SubSubCategory::where("sub_category_id",$categoryId)
									->get();

			if (!$subSubCategories) {
				return jsonResponse(false, 'Sub Sub Categories not found.',null, 404);               
			}

			return jsonResponse(true, 'Sub Sub Categories fetched successfully.',['subSubCategories' => $subSubCategories], 200);   
		}

		return jsonResponse(false, 'Sub Sub Categories not found.',null, 404);               

	}
}
