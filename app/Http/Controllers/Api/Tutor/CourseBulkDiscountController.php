<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Models\CourseBulkDiscount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseBulkDiscountController extends Controller
{
    /**
     * Display a listing of course bulk discounts for authenticated tutor.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 20);
            $search = trim($request->get('search', ''));
            $status = $request->get('status');

			$tutorId = auth('sanctum')->user()->id;

            $query = CourseBulkDiscount::query()
                ->where('owner_id', $tutorId)
                ->where('owner_type', 'tutor');

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
                'message' => 'Course bulk discounts retrieved successfully',
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
    public function store(Request $request): JsonResponse
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

            // Add owner information
            $validated['owner_id'] = auth('sanctum')->user()->id;
            $validated['owner_type'] = 'tutor';

            // Create the bulk discount
            $discount = CourseBulkDiscount::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Course bulk discount created successfully',
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
    public function show(CourseBulkDiscount $courseBulkDiscount): JsonResponse
    {
        try {

			$tutorId = auth('sanctum')->user()->id;

            // Check authorization
            if ($courseBulkDiscount->owner_id !== $tutorId || $courseBulkDiscount->owner_type !== 'tutor') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            return response()->json([
                'status' => true,
                'message' => 'Course bulk discount retrieved successfully',
                'data' => [
					'discount' => $courseBulkDiscount
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
    public function update(Request $request, CourseBulkDiscount $courseBulkDiscount): JsonResponse
    {
		$tutorId = auth('sanctum')->user()->id;
        try {
            // Check authorization
            if ($courseBulkDiscount->owner_id !== $tutorId || $courseBulkDiscount->owner_type !== 'tutor') {
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
            $courseBulkDiscount->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Course bulk discount updated successfully',
                'data' => [
					'discount' => $courseBulkDiscount
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
    public function destroy(CourseBulkDiscount $courseBulkDiscount): JsonResponse
    {
		$tutorId = auth('sanctum')->user()->id;
        try {
            // Check authorization
            if ($courseBulkDiscount->owner_id !== $tutorId || $courseBulkDiscount->owner_type !== 'tutor') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $courseBulkDiscount->delete();

            return response()->json([
                'status' => true,
                'message' => 'Course bulk discount deleted successfully',
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

    /**
     * Get bulk discounts for a specific course.
     */
    public function getByCollege(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 20);
            $tutorId = auth('sanctum')->user()->id;

            $discounts = CourseBulkDiscount::where('owner_id', $tutorId)
                ->where('owner_type', 'tutor')
                ->where('status', 'Active')
                ->orderBy('min_quantity', 'ASC')
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'Course bulk discounts retrieved successfully',
                'data' => $discounts,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve bulk discounts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
