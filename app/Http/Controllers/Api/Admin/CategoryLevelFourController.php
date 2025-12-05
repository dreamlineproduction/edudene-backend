<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryLevelFour;
use Illuminate\Http\Request;

class CategoryLevelFourController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {       

		$subSubCategories = CategoryLevelFour::query();

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

		$paginated = $subSubCategories->with(['category', 'categoryLevelTwo','categoryLevelThree'])
						->paginate($perPage, ['*'], 'page', $page);

		 return jsonResponse(true, 'Program list.', [
			'subSubSubCategories' => $paginated->items(),
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
            'sub_sub_category_id' => 'required|exists:sub_sub_categories,id',
            'title' => 'required|string|max:150',
            'status' => 'required|in:Active,Inactive',
        ]);

        $request->merge([
            'slug' => generateUniqueSlug($request->slug,'App\Models\CategoryLevelFour'),
        ]);

        $data = CategoryLevelFour::create($request->toArray());
        return jsonResponse(true, 'Program created successfully.', $data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $data = CategoryLevelFour::where('id',$id)->with(['category','categoryLevelTwo','categoryLevelThree'])->first();

        if (!$data) {
            return jsonResponse(false, 'Program not found in our database.', null, 404);
        }        

        return jsonResponse(true, 'Program details.', ['subCategoryLevelFour' => $data]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'sub_sub_category_id' => 'required|exists:sub_sub_categories,id',
            'title' => 'required|string|max:150',
            'status' => 'required|in:Active,Inactive',
        ]);

        $data = CategoryLevelFour::find($id);

        if (!$data) {
            return jsonResponse(false, 'Program not found in our database.', null, 404);
        }

        $request->merge([
            'slug' => generateUniqueSlug($request->slug,'App\Models\CategoryLevelFour',$id),
        ]);
        $data->update($request->toArray());
        return jsonResponse(true, 'Program updated successfully.', $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $data = CategoryLevelFour::find($id);

        if (!$data) {
            return jsonResponse(false, 'Program not found in our database.', null, 404);
        }

        $data->delete();
        return jsonResponse(true, 'Program deleted successfully.');
    }
}
