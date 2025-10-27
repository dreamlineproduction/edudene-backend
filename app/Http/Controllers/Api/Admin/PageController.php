<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    //

    // Get all pages
    public function index()
    {
        $pages = Page::latest()->get();

        return jsonResponse(true, 'Page list', $pages);
    }


    // Store a new page
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:pages,slug',
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_keyword' => 'nullable|string',
            'is_show' => 'required|in:Header,Footer,Both',
            'status' => 'required|in:Active,Inactive',
        ]);

        $request->merge([
            'slug' => generateUniqueSlug($request->title, 'App\Models\Page', null, 'slug')
        ]);

        $data = Page::create($request->toArray());
        return jsonResponse(true, 'Page created successfully', $data);
    }

    // Show single page
    public function show($id)
    {
        $data = Page::find($id);
        if (!$data) {
            return jsonResponse(true, 'Page not found in our database', null, 404);
        }

        return jsonResponse(true, 'Page list', $data);
    }

    // Update page
    public function update(Request $request, $id)
    {
        $page = Page::find($id);

        if (!$page) {
            return jsonResponse(false, 'Page not found in our database', null, 404);
        }
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:pages,slug,' . $id,
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_keyword' => 'nullable|string',
            'is_show' => 'required|in:Header,Footer,Both',
            'status' => 'required|in:Active,Inactive',
        ]);
        $request->merge([
            'slug' => generateUniqueSlug($request->title, 'App\Models\Page', $page->id, 'slug')
        ]);

        $page->update($request->toArray());
        return jsonResponse(true, 'Page updated successfully', $page);
    }

    // Delete page
    public function destroy($id)
    {
        $page = Page::find($id);
        if (!$page) {
            return jsonResponse(false, 'Page not found in our database', null, 404);
        }

        $page->delete();

        return jsonResponse(true, 'Page deleted successfully', null);
    }
}
