<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = Coupon::query();
        
        if ($request->has('type')) {
            $data->where('type', $request->type);
        }

        if ($request->has('redeem')) {
            $data->where('is_redeem', $request->redeem);
        }

        $data = $data->latest()->get();
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
        ];
        
        if($request->type == 'Percentage'){
            $validation['percentage'] = 'required|numeric|min:1|max:100';
        } else {
            $validation['amount'] = 'required|numeric|min:1';
        }

        $request->validate($validation);

        if($request->has('number_of_coupon') > 0){
            for($i=0; $i < $request->number_of_coupon; $i++){
                Coupon::create($request->all());
            }
            return jsonResponse(true, 'Coupons created successfully',null);           
        } else {
            return jsonResponse(false, 'Number of coupon must be at least 1', null, 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Coupon::find($id);
        if (!$data) {
            return jsonResponse(false, 'Coupon not found', null, 404);
        }

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
        
        if($request->type == 'percentage'){
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
