<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopularSubCategory;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class PopularSubCategoryController extends Controller
{
    /**
     * Display a listing of the popular sub-categories.
     */
    public function index(Request $request)
    {
        $query = PopularSubCategory::with('subCategory.category');
        
        // Filter by category_id if provided
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->whereHas('subCategory', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        
        $data = $query->orderBy('sort_order')->get();
        
        return jsonResponse(true, 'Popular sub-categories fetched successfully', ['popular_sub_categories' => $data]);
    }

    /**
     * Store a newly created popular sub-category in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'sub_category_id' => 'required|exists:sub_categories,id|unique:popular_sub_categories,sub_category_id',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:Active,Inactive',
        ], [
            'sub_category_id.required' => 'The sub-category is required.',
            'sub_category_id.exists' => 'The selected sub-category does not exist.',
            'sub_category_id.unique' => 'This sub-category is already marked as popular.',
        ]);


        // Check if sub-category exists and is active
        $subCategory = SubCategory::find($request->sub_category_id);
        if (!$subCategory) {
            return jsonResponse(false, 'The selected sub-category does not exist in the database.', null, 404);
        }

        $data = PopularSubCategory::create([
            'sub_category_id' => $request->sub_category_id,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->status,
        ]);

        $data->load('subCategory.category');
        return jsonResponse(true, 'Popular sub-category created successfully', $data, 201);
    }

    /**
     * Display the specified popular sub-category.
     */
    public function show(string $id)
    {
        $data = PopularSubCategory::with('subCategory.category')->find($id);

        if (!$data) {
            return jsonResponse(false, 'Popular sub-category not found', null, 404);
        }

        return jsonResponse(true, 'Popular sub-category details', ['popular_sub_category' => $data]);
    }

    /**
     * Update the specified popular sub-category in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = PopularSubCategory::find($id);

        if (!$data) {
            return jsonResponse(false, 'Popular sub-category not found', null, 404);
        }

        $request->validate([
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:Active,Inactive',
        ]);

        $data->update([
            'sort_order' => $request->sort_order ?? $data->sort_order,
            'status' => $request->status,
        ]);

        $data->load('subCategory.category');
        return jsonResponse(true, 'Popular sub-category updated successfully', $data);
    }

    /**
     * Remove the specified popular sub-category from storage.
     */
    public function destroy(string $id)
    {
        $data = PopularSubCategory::find($id);

        if (!$data) {
            return jsonResponse(false, 'Popular sub-category not found', null, 404);
        }

        $data->delete();

        return jsonResponse(true, 'Popular sub-category deleted successfully', null, 200);
    }

    /**
     * Get all active popular sub-categories with their relations.
     * This can be used by the frontend to display popular categories.
     */
    public function getActiveFrontend(Request $request)
    {
      
        $query = PopularSubCategory::with('subCategory.category');
        
        // Filter by category_id if provided
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->whereHas('subCategory', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        
        $data = $query->orderBy('sort_order')
            ->where('status', 'Active')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'sub_category_id' => $item->sub_category_id,
                    'sort_order' => $item->sort_order,
                    'status' => $item->status,

                    // Merge subCategory fields
                    'category_id' => $item->subCategory->category_id,
                    'title' => $item->subCategory->title,
                    'slug' => $item->subCategory->slug,
                    'is_popular' => $item->subCategory->is_popular,
                    'popular_order' => $item->subCategory->popular_order,

                    // Keep category nested
                    'category' => $item->subCategory->category
                ];
            });

        return jsonResponse(true, 'Active popular sub-categories fetched successfully', 
            ['popular_sub_categories' => $data]
        );
    }
}
