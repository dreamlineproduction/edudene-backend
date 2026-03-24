<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Language;


class LanguageController extends Controller
{
    //

    public function index(Request $request)
    {
        $pages = Language::query();

        if (!empty($request->search)) {
            $pages = $pages->where('title','like','%'.$request->search.'%');
        }

        $sortBy = $request->get('sort_by');
        $sortDirection = $request->get('sort_direction', 'asc');

        if (in_array($sortBy, ['id', 'title', 'status', 'created_at'])) {
            $pages = $pages->orderBy($sortBy, $sortDirection);
        } else {
            $pages = $pages->orderBy('id', 'DESC');
        }

        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        $paginated = $pages->paginate($perPage, ['*'], 'page', $page);

        return jsonResponse(true, 'Language fetched successfully', [
            'languages' => $paginated->items(),
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:languages,code',
            'status' => 'required|in:Active,Inactive',
        ]);

        $language = Language::create($request->all());

        return jsonResponse(true, 'Language created successfully');       
    }

    public function show($id)
    {
        $language = Language::find($id);

        if (!$language) {          
            return jsonResponse(false, 'Language not found',[],404);
        }

        return jsonResponse(true, 'Language show',[
            'language' => $language
        ]);              
    }

    public function update(Request $request, $id)
    {
        $language = Language::find($id);

        if (!$language) {
            return jsonResponse(false, 'Language not found',[],404);
        }

        $request->validate([
            'title' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:languages,code,' . $id,
            'status' => 'required|in:Active,Inactive',
        ]);

        $language->update($request->all());

        return jsonResponse(true, 'Language updated successfully');       
    }

    public function destroy($id)
    {
        $language = Language::find($id);

        if (!$language) {
            return jsonResponse(false, 'Language not found',[],404);
        }

        $language->delete();

        return jsonResponse(true, 'Language deleted successfully');     
    }
}
