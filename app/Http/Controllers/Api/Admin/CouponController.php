<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);

        $batchQuery = Coupon::select('*')
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->when($request->filled('redeem'), function ($q) use ($request) {
                $q->where('is_redeem', $request->redeem);
            })
            ->when($request->filled('title'), function ($q) use ($request) {
                $q->where('title', $request->redeem);
            })
            ->distinct()
            ->orderBy('batch_number', 'desc');

        $paginatedBatches = $batchQuery->paginate($perPage);

        $batchDetails = $paginatedBatches->getCollection()->map(function ($batch) {
            $items = Coupon::where('batch_number', $batch->batch_number)->get();
            $first = $items->first();

            return [
                'batch_number'   => $batch->batch_number,
                'title'          => $first->title,
                'type'           => $first->type,
                'amount'         => $first->amount,
                'created_by'     => $first->createdBy?->name ?? 'Admin',
                'total_coupons'  => $items->count(),
                'total_redeemed' => $items->where('is_redeem', 1)->count(),
            ];
        });
        $paginatedBatches->setCollection($batchDetails);

        $data = $paginatedBatches;
        return jsonResponse(true, 'Coupon list', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validation = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:Fixed,Percentage',
            'number_of_coupon' => 'required|integer|min:1',
            'status' => 'in:Active,Inactive',
            'amount' => 'required|numeric|min:1',
        ];



        $request->validate($validation);

        if ($request->filled('number_of_coupons') && $request->number_of_coupons > 0) {

            $batchNumber = 'BADGE-' . rand(100000, 999999);

            for ($i = 0; $i < $request->number_of_coupons; $i++) {
                do {
                    $code = strtoupper(Str::random(8));
                } while (Coupon::where('code', $code)->exists());

                Coupon::create([
                    'title'        => $request->title,
                    'type'         => $request->type,
                    'amount'       => $request->amount,
                    'batch_number' => $batchNumber,
                    'code'         => $code,
                    'status'       => $request->status
                ]);
            }

            return jsonResponse(true, 'Coupons created successfully');
        } else {
            return jsonResponse(false, 'Number of coupon must be at least 1', null, 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $batchNumber)
    {
        $data = Coupon::where('batch_number', $batchNumber)->get();
        if ($data->isEmpty()) {
            return jsonResponse(false, 'Coupon not found', null, 404);
        }

        $data->map(function ($item) {
            $item->created_by = 'Admin';
            $item->redeem_by = null;
        });

        return jsonResponse(true, 'Coupon details', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $data = Coupon::find($id);
        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Coupon not found'], 404);
        }

        $validation = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:Fixed,Percentage',
            'status' => 'in:Active,Inactive',
        ];

        if ($request->type == 'percentage') {
            $validation['percentage'] = 'required|numeric|min:1|max:100';
        } else {
            $validation['amount'] = 'required|numeric|min:1';
        }

        $request->validate($validation);

        $data->update($request->all());

        return jsonResponse(true, 'Coupon updated successfully', $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $data = Coupon::find($id);
        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Coupon not found'], 404);
        }

        $data->delete();
        return response()->json(['status' => true, 'message' => 'Coupon deleted successfully']);
    }
}
