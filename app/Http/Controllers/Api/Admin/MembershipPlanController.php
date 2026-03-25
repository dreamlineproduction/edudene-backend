<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Models\PlanFeature;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $plans = MembershipPlan::with('planFeatures.feature')->orderBy('created_at', 'desc')->get();

            return jsonResponse(true, 'Membership plans fetched successfully', ['plans' => $plans]);
        } catch (\Exception $e) {
            return jsonResponse(false, 'Error fetching membership plans: ' . $e->getMessage(), [], 500);
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
                'interval' => 'required|in:monthly,quarterly,yearly',
                'price' => 'required|numeric|min:0',
                'status' => 'required|in:active,block',
                'features' => 'sometimes|array',
                'features.*.feature_id' => 'required_with:features|integer|exists:features,id',
                'features.*.value' => 'nullable|string',
                'features.*.label' => 'nullable|string',
            ]);

            $plan = MembershipPlan::create([
                'name' => $request->name,
                'interval' => $request->interval,
                'price' => $request->price,
                'status' => $request->status,
            ]);

            // Add features if provided
            if ($request->has('features')) {
                foreach ($request->features as $feature) {
                    PlanFeature::create([
                        'plan_id' => $plan->id,
                        'feature_id' => $feature['feature_id'],
                        'value' => $feature['value'] ?? null,
                        'label' => $feature['label'] ?? null,
                    ]);
                }
            }

            $plan = $plan->load('planFeatures.feature');

            return jsonResponse(true, 'Membership plan created successfully', ['plan' => $plan], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return jsonResponse(false, 'Validation error', $e->errors(), 422);
        } catch (\Exception $e) {
            return jsonResponse(false, 'Error creating membership plan: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $plan = MembershipPlan::with('planFeatures.feature')->findOrFail($id);

            return jsonResponse(true, 'Membership plan fetched successfully', ['plan' => $plan]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return jsonResponse(false, 'Membership plan not found', [], 404);
        } catch (\Exception $e) {
            return jsonResponse(false, 'Error fetching membership plan: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $plan = MembershipPlan::findOrFail($id);

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'interval' => 'sometimes|in:monthly,quarterly,yearly',
                'price' => 'sometimes|numeric|min:0',
                'status' => 'sometimes|in:active,block',
                'features' => 'sometimes|array',
                'features.*.feature_id' => 'required_with:features|integer|exists:features,id',
                'features.*.value' => 'nullable|string',
                'features.*.label' => 'nullable|string',
            ]);

            $plan->update($request->only(['name', 'interval', 'price', 'status']));

            // Update features if provided
            if ($request->has('features')) {
                // Delete existing features
                PlanFeature::where('plan_id', $id)->delete();

                // Add new features
                foreach ($request->features as $feature) {
                    PlanFeature::create([
                        'plan_id' => $plan->id,
                        'feature_id' => $feature['feature_id'],
                        'value' => $feature['value'] ?? null,
                        'label' => $feature['label'] ?? null,
                    ]);
                }
            }

            $plan = $plan->load('planFeatures.feature');

            return jsonResponse(true, 'Membership plan updated successfully', ['plan' => $plan]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return jsonResponse(false, 'Validation error', $e->errors(), 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return jsonResponse(false, 'Membership plan not found', [], 404);
        } catch (\Exception $e) {
            return jsonResponse(false, 'Error updating membership plan: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $plan = MembershipPlan::findOrFail($id);
            $plan->delete();

            return jsonResponse(true, 'Membership plan deleted successfully', []);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return jsonResponse(false, 'Membership plan not found', [], 404);
        } catch (\Exception $e) {
            return jsonResponse(false, 'Error deleting membership plan: ' . $e->getMessage(), [], 500);
        }
    }
}
