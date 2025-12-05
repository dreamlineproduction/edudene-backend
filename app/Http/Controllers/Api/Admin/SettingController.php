<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function saveWebsite(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'phone_number' => 'required|string|min:10|max:15',
            'dark_logo_id' => 'integer',
            'light_logo_id' => 'integer',
            'favicon_logo_id' => 'integer',
        ]);

        $setting = WebsiteSetting::find(1);

        $newPath = 'settings';
        // Save dark logo
        if (notEmpty($request->dark_logo_id)) {

            // Check is file already exists.
            if (notEmpty($setting->dark_logo)) {
                // Delete old image
                deleteS3File($setting->dark_logo);
            }

            $image = finalizeFile($request->dark_logo_id,$newPath);

            $request->merge([
                'dark_logo' => $image['path'],
                'dark_logo_url' => $image['url']
            ]);
        }


        // Save light logo
        if (notEmpty($request->light_logo_id)) {

            // Check is file already exists.
            if (notEmpty($setting->light_logo)) {
                // Delete old image
                deleteS3File($setting->light_logo);
            }

           
            $image = finalizeFile($request->light_logo_id,$newPath);

            $request->merge([
                'light_logo' => $image['path'],
                'light_logo_url' => $image['url']
            ]);
        }


        // Save favicon logo
        if (notEmpty($request->favicon_logo_id)) {
            
            // Check is file already exists.
            if (notEmpty($setting->favicon_logo)) {
                // Delete old image
                deleteS3File($setting->favicon_logo);
            }

            $image = finalizeFile($request->favicon_logo_id,$newPath);
            $request->merge([
                'favicon_logo' => $image['path'],
                'favicon_logo_url' => $image['url']
            ]);

        }

        $data = WebsiteSetting::updateOrCreate(['id' => 1], $request->toArray());
        return jsonResponse(true, 'Setting has been updated successfully.',$data);
    }


    public function savePayment(Request $request){
        $request->validate([
            'stripe_status' => 'required|in:Active,Inactive',
            'stripe_use' => 'required|in:Test,Live',
            'stripe_secret_key' => 'required|string',
            'stripe_public_key' => 'required|string',
            'test_stripe_secret_key' => 'required|string',
            'test_stripe_public_key' => 'required|string',
        ]);


        $data = PaymentSetting::updateOrCreate(['id' => 1], $request->toArray());
        return jsonResponse(true, 'Setting has been updated successfully.',$data);
    }

    public function show(Request $request, $id = 1){
        $setting = WebsiteSetting::find($id);
        $data['website_setting'] = notEmpty($setting) ? $setting : [];

        $setting = PaymentSetting::find($id);
        $data['payment_setting'] = notEmpty($setting) ? $setting: [];
        return jsonResponse(true, 'Setting has been updated successfully.', $data);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
