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
    public function index(Request $request)
    {
        $categories = Category::query();

		if (!empty($request->search)) {
			$categories = $categories->where('title','like','%'.$request->search.'%');
		}

		$sortBy = $request->get('sort_by', 'title');
    	$sortDirection = $request->get('sort_direction', 'asc');

		if (in_array($sortBy, ['id', 'title', 'slug', 'status', 'created_at'])) {
			$categories = $categories->orderBy($sortBy, $sortDirection);
		} else {
			$categories = $categories->orderBy('title', 'asc');
		}

		$perPage = (int) $request->get('per_page', 10);
    	$page = (int) $request->get('page', 1);

		$paginated = $categories->paginate($perPage, ['*'], 'page', $page);

		 return jsonResponse(true, 'Categories fetched successfully', [
			'categories' => $paginated->items(),
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
        $request->validate([
            'title' => 'required|string|max:200',
            'status' => 'required|in:Active,Inactive',
        ]);
       
        $category = Category::create([
            'title' => $request->title,
			'status' => $request->status,
            'slug' => generateUniqueSlug($request->slug,'App\Models\Category'),
        ]);

        return jsonResponse(true,'Category created successfully',$category);         
    }


    public function show(string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return jsonResponse(false,'Category not found in our database.',$category,404);            
        }
        return jsonResponse(true,'Category details',['category' => $category]);
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
            'slug' => generateUniqueSlug($request->slug,'App\Models\Category',$category->id),
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
