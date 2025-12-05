<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaqSection;


class FaqSectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $data = FaqSection::latest()->get();
        // return jsonResponse(true, 'Faq section fetched successfully', ['sections' => $data]);

		$faqsSection = FaqSection::query();

		if (!empty($request->search)) {
			$faqsSection = $faqsSection->where('title','like','%'.$request->search.'%');
		}

		$sortBy = $request->get('sort_by', 'title');
    	$sortDirection = $request->get('sort_direction', 'asc');

		if (in_array($sortBy, ['id', 'title', 'status', 'created_at'])) {
			$faqsSection = $faqsSection->orderBy($sortBy, $sortDirection);
		} else {
			$faqsSection = $faqsSection->orderBy('title', 'asc');
		}

		$perPage = (int) $request->get('per_page', 10);
    	$page = (int) $request->get('page', 1);

		$paginated = $faqsSection->paginate($perPage, ['*'], 'page', $page);

		 return jsonResponse(true, 'Faq section fetched successfully', [
			'sections' => $paginated->items(),
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
            'title'          => 'required|string|max:255',
            'status'         => 'required|in:Active,Inactive',
        ]);

        $data = FaqSection::create($request->toArray());
        return jsonResponse(true, 'Faq section created successfully', $data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = FaqSection::where('id',$id)->first();
        if (!$data) {
            return jsonResponse(false,'Faq section not found in our database.',$data,404);            
        }

        return jsonResponse(true, 'Faq section fetched successfully', ['section' => $data]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = FaqSection::where('id',$id)->first();
        if (!$data) {
            return jsonResponse(false,'Faq section not found in our database.',$data,404);   
        }

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'status'         => 'required|in:Active,Inactive',
        ]);


        $data->update($request->toArray());
        return jsonResponse(true, 'Faq section updated successfully', $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $data = FaqSection::where('id',$id)->first();
        if (!$data) {
            return jsonResponse(false,'Faq section not found in our database.',$data,404);   
        }

        $data->delete();
        return jsonResponse(true, 'Faq section deleted successfully');
    }
}
