<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // $features = Feature::orderBy('created_at', 'desc')->get();

			$features = Feature::query();

			if (!empty($request->search)) {
				$features = $features->where('name','like','%'.$request->search.'%');
			}

			$sortBy = $request->get('sort_by');
    		$sortDirection = $request->get('sort_direction', 'asc');

			if (in_array($sortBy, ['id', 'name', 'status', 'created_at'])) {
				$features = $features->orderBy($sortBy, $sortDirection);
			} else {
				$features = $features->orderBy('id', 'asc');
			}

			$perPage = (int) $request->get('per_page', 10);
			$page = (int) $request->get('page', 1);

			$paginated = $features->paginate($perPage, ['*'], 'page', $page);

			return jsonResponse(true, 'Features fetched successfully', [
				'features' => $paginated->items(),
				'total' => $paginated->total(),
				'current_page' => $paginated->currentPage(),
				'per_page' => $paginated->perPage(),
			]);            
        } catch (\Exception $e) {
            return jsonResponse(false, 'Error fetching features: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:255|unique:features,code',
				'type' => 'required',
            ]);

            $feature = Feature::create([
                'name' => $request->name,
                'code' => $request->code,
				'type' => $request->type,
            ]);

            return jsonResponse(true, 'Feature created successfully', ['feature' => $feature], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return jsonResponse(false, 'Validation error', $e->errors(), 422);
        } catch (\Exception $e) {
            return jsonResponse(false, 'Error creating feature: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $feature = Feature::findOrFail($id);
            
            return jsonResponse(true, 'Feature fetched successfully', ['feature' => $feature]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return jsonResponse(false, 'Feature not found', [], 404);
        } catch (\Exception $e) {
            return jsonResponse(false, 'Error fetching feature: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $feature = Feature::findOrFail($id);

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'code' => 'sometimes|string|max:255|unique:features,code,' . $id,
				'type' => 'required',
            ]);

            $feature->update($request->only(['name', 'code','type']));

            return jsonResponse(true, 'Feature updated successfully', ['feature' => $feature]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return jsonResponse(false, 'Validation error', $e->errors(), 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return jsonResponse(false, 'Feature not found', [], 404);
        } catch (\Exception $e) {
            return jsonResponse(false, 'Error updating feature: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $feature = Feature::findOrFail($id);
            $feature->delete();

            return jsonResponse(true, 'Feature deleted successfully', []);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return jsonResponse(false, 'Feature not found', [], 404);
        } catch (\Exception $e) {
            return jsonResponse(false, 'Error deleting feature: ' . $e->getMessage(), [], 500);
        }
    }
}
