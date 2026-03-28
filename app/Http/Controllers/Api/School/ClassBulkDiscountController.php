<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Models\ClassBulkDiscount;
use App\Models\SchoolUser;
use Illuminate\Http\Request;

class ClassBulkDiscountController extends Controller
{
    /**
     * Display a listing of course bulk discounts for authenticated tutor.
     */
    public function index(Request $request)
    {
		$tutorId = auth('sanctum')->user()->id;
		$schoolInfo = SchoolUser::where('user_id', $tutorId)->first();

        try {
            $perPage = (int) $request->get('per_page', 20);
            $search = trim($request->get('search', ''));
            $status = $request->get('status');

			$query = ClassBulkDiscount::query()
                ->where('owner_id', $schoolInfo->school_id)
                ->where('owner_type', 'school');

            // Search by title or text
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('text', 'LIKE', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $status);
            }

            $discounts = $query
                ->orderBy('created_at', 'DESC')
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'Class bulk discounts retrieved successfully',
                'data' => [
					'discounts' => $discounts
				],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve bulk discounts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'text' => 'nullable|string',
                'min_quantity' => 'required|integer|min:1',
                'max_quantity' => 'nullable|integer|min:1|gte:min_quantity',
                'discount_percentage' => 'required|numeric|min:0|max:100',
                'status' => 'nullable|in:Active,Inactive',
            ]);


			$tutorId = auth('sanctum')->user()->id;
			$schoolInfo = SchoolUser::where('user_id', $tutorId)->first();

            // Add owner information
            $validated['owner_id'] = $schoolInfo->school_id;
            $validated['owner_type'] = 'school';

            // Create the bulk discount
            $discount = ClassBulkDiscount::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Class bulk discount created successfully',
                'data' => [
					'discount' => $discount
				],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create bulk discount',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassBulkDiscount $classBulkDiscount)
    {
        try {

			$tutorId = auth('sanctum')->user()->id;
			$schoolInfo = SchoolUser::where('user_id', $tutorId)->first();

            // Check authorization
            if ($classBulkDiscount->owner_id !== $schoolInfo->school_id || $classBulkDiscount->owner_type !== 'school') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            return response()->json([
                'status' => true,
                'message' => 'Course bulk discount retrieved successfully',
                'data' => [
					'discount' => $classBulkDiscount
				],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve bulk discount',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClassBulkDiscount $classBulkDiscount)
    {
		$tutorId = auth('sanctum')->user()->id;
		$schoolInfo = SchoolUser::where('user_id', $tutorId)->first();

        try {
            // Check authorization
            if ($classBulkDiscount->owner_id !== $schoolInfo->school_id || $classBulkDiscount->owner_type !== 'school') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Validate input
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'text' => 'nullable|string',
                'min_quantity' => 'sometimes|required|integer|min:1',
                'max_quantity' => 'nullable|integer|min:1|gte:min_quantity',
                'discount_percentage' => 'sometimes|required|numeric|min:0|max:100',
                'status' => 'nullable|in:Active,Inactive',
            ]);

            // Update the bulk discount
            $classBulkDiscount->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Class bulk discount updated successfully',
                'data' => [
					'discount' => $classBulkDiscount
				],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update bulk discount',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassBulkDiscount $classBulkDiscount)
    {
		$tutorId = auth('sanctum')->user()->id;
		$schoolInfo = SchoolUser::where('user_id', $tutorId)->first();

        try {
            // Check authorization
            if ($classBulkDiscount->owner_id !== $schoolInfo->school_id || $classBulkDiscount->owner_type !== 'school') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $classBulkDiscount->delete();

            return response()->json([
                'status' => true,
                'message' => 'Class bulk discount deleted successfully',
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete bulk discount',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
