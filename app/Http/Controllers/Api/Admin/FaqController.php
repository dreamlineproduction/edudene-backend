<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $faqs = Faq::query();

		if (!empty($request->search)) {
			$faqs = $faqs->where('title','like','%'.$request->search.'%');
		}

		$sortBy = $request->get('sort_by', 'title');
    	$sortDirection = $request->get('sort_direction', 'asc');

		if (in_array($sortBy, ['id', 'title', 'status', 'created_at'])) {
			$faqs = $faqs->orderBy($sortBy, $sortDirection);
		} else {
			$faqs = $faqs->orderBy('title', 'asc');
		}

		$perPage = (int) $request->get('per_page', 10);
    	$page = (int) $request->get('page', 1);

		$paginated = $faqs->with('section')->paginate($perPage, ['*'], 'page', $page);

		return jsonResponse(true, 'Faqs fetched successfully', [
			'faqs' => $paginated->items(),
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
        $validated = $request->validate([
            'faq_section_id' => 'required|exists:faq_sections,id',
            'title'          => 'required|string|max:255',
            'status'         => 'required|in:Active,Inactive',
            'is_home'        => 'required|in:Yes,No',
        ]);

        $data = Faq::create($request->toArray());
        return jsonResponse(true, 'Faq created successfully', $data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $data = Faq::with('section')->where('id',$id)->first();
        if (!$data) {
            return jsonResponse(false,'Faq not found in our database.',$data,404);            
        }

        return jsonResponse(true, 'Faq fetched successfully', ['faq' => $data]);
    }

    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = Faq::where('id',$id)->first();
        if (!$data) {
            return jsonResponse(false,'Faq not found in our database.',$data,404);   
        }

        $validated = $request->validate([
            'faq_section_id' => 'required|exists:faq_sections,id',
            'title'          => 'required|string|max:255',
            'status'         => 'required|in:Active,Inactive',
            'is_home'        => 'required|in:Yes,No',
        ]);

        $data->update($request->toArray());
        return jsonResponse(true, 'Faq updated successfully', $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $data = Faq::where('id',$id)->first();
        if (!$data) {
            return jsonResponse(false,'Faq not found in our database.',$data,404);   
        }

        $data->delete();
        return jsonResponse(true, 'Faq deleted successfully');
    }
}
