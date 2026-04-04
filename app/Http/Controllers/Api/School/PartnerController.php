<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartnerRequest;
use App\Models\Partner;
use App\Models\SchoolUser;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    /**
     * Display a listing of the partners.
     */
    public function index()
    {	
		// Get authenticated user details
		$tutorId = auth('sanctum')->user()->id;

		// Fetch school association for non-tutor users (e.g., school admin/staff)
		$schoolInfo = SchoolUser::where('user_id', $tutorId)->first();

		// Return error if user is not associated with any school
		if ($schoolInfo == null) {
			return jsonResponse(false, 'School not found', [], 404);
		}
		
        try {
            $partners = Partner::where('school_id',$schoolInfo->school_id)->paginate(15);
            
            return response()->json([
                'status' => true,
                'message' => 'Partners retrieved successfully',
                'data' => $partners
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving partners',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created partner in storage.
     */
    public function store(PartnerRequest $request)
    {
        try {
			$partner = Partner::create($request->validated());
            
            return response()->json([
                'status' => true,
                'message' => 'Partner created successfully',
                'data' => $partner
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error creating partner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified partner.
     */
    public function show(Partner $partner)
    {
        try {

			$tutorId = auth('sanctum')->user()->id;
			$schoolInfo = SchoolUser::where('user_id',$tutorId)->first();

			$partner = Partner::where('id', $partner->id)
				->where('school_id', $schoolInfo->school_id) 
				->first();

			if (!$partner) {
				return response()->json([
					'status' => false,
					'message' => 'Partner not found or unauthorized'
				], 404);
			}

            return response()->json([
                'status' => true,
                'message' => 'Partner retrieved successfully',
                'data' => $partner
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving partner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified partner in storage.
     */
    public function update(PartnerRequest $request, Partner $partner)
    {
        try {
            $partner->update($request->validated());
            
            return response()->json([
                'status' => true,
                'message' => 'Partner updated successfully',
                'data' => $partner
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error updating partner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified partner from storage.
     */
    public function destroy(Partner $partner)
    {
        try {

			$tutorId = auth('sanctum')->user()->id;
			$schoolInfo = SchoolUser::where('user_id',$tutorId)->first();

			$partner = Partner::where('id', $partner->id)
				->where('school_id', $schoolInfo->school_id) 
				->first();

            $partner->delete();
            
            return response()->json([
                'status' => true,
                'message' => 'Partner deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting partner',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
