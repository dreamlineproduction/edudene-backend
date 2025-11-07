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
    public function index()
    {
        //
        $data = CategoryLevelFour::with(['category','categoryLevelTwo','categoryLevelThree'])->get();
        return jsonResponse(true, 'Category Level Four list.',$data);
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
            'slug' => generateUniqueSlug($request->title,'App\Models\CategoryLevelFour'),
        ]);

        $data = CategoryLevelFour::create($request->toArray());
        return jsonResponse(true, 'Category Level Four created successfully.', $data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $data = CategoryLevelFour::where('id',$id)->with(['category','categoryLevelTwo','categoryLevelThree'])->first();

        if (!$data) {
            return jsonResponse(false, 'Category Level Four not found in our database.', null, 404);
        }        

        return jsonResponse(true, 'Category Level Four details.', $data);
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
            return jsonResponse(false, 'Category Level Four not found in our database.', null, 404);
        }

        $request->merge([
            'slug' => generateUniqueSlug($request->title,'App\Models\CategoryLevelFour',$id),
        ]);
        $data->update($request->toArray());
        return jsonResponse(true, 'Category Level Four updated successfully.', $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $data = CategoryLevelFour::find($id);

        if (!$data) {
            return jsonResponse(false, 'Category Level Four not found in our database.', null, 404);
        }

        $data->delete();
        return jsonResponse(true, 'Category Level Four deleted successfully.');
    }
}
